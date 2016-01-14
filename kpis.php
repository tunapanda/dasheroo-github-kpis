<?php

	/**
	 * Request arguments for this script:
	 *
	 * - projects
	 *   Comma separated list uf github projects to consider.
	 *
	 * - labels
	 *   Comma separated list of labels.
	 *
	 * - state
	 *   open, closed or all.
	 *
	 * - assigned
	 *   true, false or all.
	 *
	 * - closed_last_days
	 *   <number> Consider only issues closed during these last days.
	 *
	 * - updated_last_days
	 *   <number> Consider only issues updated during these last days.
	 *
	 * - opened_last_days
	 *   <number> Consider only issues opened during these last days.
	 */

	function handleException($exception) {
		$res=array(
			"error"=>true,
			"message"=>$exception->getMessage()
		);

		http_response_code(500);
		echo json_encode($res,JSON_PRETTY_PRINT);
		exit;
	}

	set_exception_handler("handleException");

	if (!isset($_REQUEST["projects"]))
		throw new Exception("No projects specified");

	$projectNames=explode(",",$_REQUEST["projects"]);
	$issues=[];
	foreach ($projectNames as $projectName) {
		$projectName=trim($projectName);
		$args=array();

		if (isset($_REQUEST["labels"]))
			$args["labels"]=$_REQUEST["labels"];

		if (isset($_REQUEST["state"]))
			$args["state"]=$_REQUEST["state"];

		$url="https://api.github.com/repos/".$projectName."/issues";
		$url.="?".http_build_query($args);

		$curl=curl_init($url);
		curl_setopt($curl,CURLOPT_USERPWD,file_get_contents(".githubuserpwd"));
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl,CURLOPT_HTTPHEADER,array(
			"User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1521.3 Safari/537.36"
		));

		$doc=curl_exec($curl);
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);

		$projectIssues=json_decode($doc,TRUE);

		if ($projectIssues===NULL)
			throw new Exception("Unable to parse json.");

		if ($code!=200 || isset($projectIssues["message"]))
			throw new Exception($projectIssues["message"]);

		foreach ($projectIssues as $projectIssue)
			$issues[]=$projectIssue;
	}

	$count=0;
	foreach ($issues as $issue) {
		$countThis=TRUE;

		if (isset($_REQUEST["assigned"])) {
			switch ($_REQUEST["assigned"]) {
				case "true":
				case "yes":
					if (!$issue["assignee"])
						$countThis=FALSE;
					break;

				case "false":
				case "no":
					if ($issue["assignee"])
						$countThis=FALSE;
					break;

				case "all":
					break;

				default:
					throw new Exception("Unrecognized value for 'assigned'");
			}
		}

		$now=time();

		if (isset($_REQUEST["closed_last_days"])) {
			if (!$issue["closed_at"] ||
					strtotime($issue["closed_at"])<$now-60*60*24*$_REQUEST["closed_last_days"])
				$countThis=FALSE;
		}

		if (isset($_REQUEST["created_last_days"])) {
			if (strtotime($issue["created_at"])<$now-60*60*24*$_REQUEST["created_last_days"])
				$countThis=FALSE;
		}

		if (isset($_REQUEST["updated_last_days"])) {
			if (strtotime($issue["updated_at"])<$now-60*60*24*$_REQUEST["updated_last_days"])
				$countThis=FALSE;
		}

		if ($countThis)
			$count++;
	}

	$label="Issues on GitHub";

	if (isset($_REQUEST["label"]))
		$label=$_REQUEST["label"];

	$res=array(
		"issues"=>array(
			"type"=>"integer",
			"value"=>$count,
			"label"=>$label,
			"strategy"=>"continuous"
		)
	);

	echo json_encode($res,JSON_PRETTY_PRINT);
