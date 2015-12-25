<?php

	function echo_attr($s) {
		echo htmlspecialchars($s);
	}

	function echo_option($value, $label, $requestKey, $requestArray=NULL) {
		if (!$requestArray)
			$requestArray=$_REQUEST;

		$s="<option value='".htmlspecialchars($value)."'";
		if ($requestArray[$requestKey]==$value)
			$s.=" selected";

		$s.=">".htmlspecialchars($label)."</option>";

		echo $s;
	}

	error_reporting(E_ALL);
	$isExample=FALSE;

	if (isset($_REQUEST["showData"])) {
		$isExample=TRUE;
		switch ($_REQUEST["example"]) {
			case "burndown":
				$_REQUEST["labels"]="current-sprint";
				$_REQUEST["state"]="open";
				$_REQUEST["assigned"]="all";
				$_REQUEST["created_last_days"]="";
				$_REQUEST["updated_last_days"]="";
				$_REQUEST["closed_last_days"]="";
				break;

			case "velocity":
				$_REQUEST["labels"]="resolved";
				$_REQUEST["state"]="closed";
				$_REQUEST["assigned"]="all";
				$_REQUEST["created_last_days"]="";
				$_REQUEST["updated_last_days"]="";
				$_REQUEST["closed_last_days"]="7";
				break;

			case "unassigned":
				$_REQUEST["labels"]="";
				$_REQUEST["state"]="open";
				$_REQUEST["assigned"]="false";
				$_REQUEST["created_last_days"]="";
				$_REQUEST["updated_last_days"]="";
				$_REQUEST["closed_last_days"]="";
				break;

			default:
				$isExample=FALSE;
				break;
		}

		$params=array();
		$params["projects"]=$_REQUEST["projects"];

		if ($_REQUEST["labels"])
			$params["labels"]=$_REQUEST["labels"];

		$params["state"]=$_REQUEST["state"];
		$params["assigned"]=$_REQUEST["assigned"];

		if ($_REQUEST["closed_last_days"])
			$params["closed_last_days"]=$_REQUEST["closed_last_days"];

		if ($_REQUEST["created_last_days"])
			$params["created_last_days"]=$_REQUEST["created_last_days"];

		if ($_REQUEST["updated_last_days"])
			$params["updated_last_days"]=$_REQUEST["updated_last_days"];

		$dataParams=array();
		if (isset($params["projects"]) && $params["projects"])
			$dataParams["projects"]=$params["projects"];

		if (isset($params["labels"]) && $params["labels"])
			$dataParams["labels"]=$params["labels"];

		if (isset($params["state"]) && $params["state"])
			$dataParams["state"]=$params["state"];

		if (isset($params["assigned"]) && $params["assigned"])
			$dataParams["assigned"]=$params["assigned"];

		if (isset($params["created_last_days"]) && $params["created_last_days"])
			$dataParams["created_last_days"]=$params["created_last_days"];

		if (isset($params["closed_last_days"]) && $params["closed_last_days"])
			$dataParams["closed_last_days"]=$params["closed_last_days"];

		if (isset($params["updated_last_days"]) && $params["updated_last_days"])
			$dataParams["updated_last_days"]=$params["updated_last_days"];

		$dirUrl="http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		$dataUrl=$dirUrl."/kpis.php?".http_build_query($dataParams);

		$curl=curl_init($dataUrl);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
		$dataResponse=curl_exec($curl);
		$dataResponse=nl2br($dataResponse);
	}

	$acceptedParams=array(
		"projects","labels","state","assigned",
		"created_last_days","updated_last_days","closed_last_days"
	);
	foreach ($acceptedParams as $acceptedParam) {
		if (!isset($_REQUEST[$acceptedParam]))
			$_REQUEST[$acceptedParam]="";
	}

	$thisLink="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<html>
	<head>
		<title>dasheroo-github-kpis</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
		<script>
			function submitExample(example) {
				document.getElementById("example-field").value=example;
				document.getElementById("data-form").submit();
				return false;
			}
			</script>
	</head>
	<body>
		<div class="header">
			dasheroo-github-kpis
		</div>

		<div class="content">
			<h1>dasheroo-github-kpis</h1>
			<p>
				You can use this tool to aggregate information about the issues
				for one or more of your
				<a href="https://github.com">GitHub</a> projects in order
				to show KPIs for your team on your 
				<a href="https://www.dasheroo.com/">Dasheroo</a>
				dashboard. 
			</p>

			<p>
				It is 
				<a href="https://github.com/tunapanda/dasheroo-github-kpis">open source</a>.
			</p>

			<h2>Setting up</h2>
			<p>
				First, create a new insight in Dasheroo, and select <i>Custom</i> as
				the app to use. Select <i>Single Stat with Histogram</i> as the sub-type.
			</p>
			<img class="image" src="img/dasheroo-create.png" style="width: 425px; height: 315px"/>
			<img class="image" src="img/dasheroo-histogram.png" style="width: 424; height: 315px"/>
			<p>
				Then, click on the settings button and locate the <i>Data URL</i> field.
			</p>
			<img class="image" src="img/dasheroo-configure.png" style="width: 275px; height: 294px"/>
			<p>
				This is where you input the URL that you construct using the tool below.
			</p>

			<?php if ($isExample) { ?>
				<a id="data"></a>
			<?php } ?>
			<h2>Data URL</h2>
			The <i>Data URL</i> depends on what data you want to measure. Experiment with
			the fields below, and click "Show Data" to see the appropriate Data URL for your
			project as well as what data that is currently returned from the GitHub API.
			<form class="form" method="post" action="<?php echo $thisLink ?>#data"
				id="data-form">
				<div class="form-row">
					<div class="form-label">
						GitHub Projects
					</div>
					<div class="form-field-holder">
						<input type="text" class="input" name="projects"
							value="<?php echo_attr($_REQUEST["projects"]); ?>"
						/>
						<p>
							Comma separated list of GitHub projects to consider. <br/>
							E.g. tunapanda/dasheroo-github-kpis
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
						Labels
					</div>
					<div class="form-field-holder">
						<input type="text" name="labels"
							value="<?php echo_attr($_REQUEST["labels"]); ?>"
						/>
						<p>
							Count issues with these labels. Comma separated.<br/>
							E.g. current-sprint or resolved.
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
						State
					</div>
					<div class="form-field-holder">
						<select name="state">
							<?php echo_option("all","Count both open and closed issues","state"); ?>
							<?php echo_option("open","Count only open issues","state"); ?>
							<?php echo_option("closed","Count only closed issues","state"); ?>
						</select>
						<p>
							Which state should be considered?
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
						Assigned
					</div>
					<div class="form-field-holder">
						<select name="assigned">
							<?php echo_option("all","Count both assigned and unassigned issues","assigned"); ?>
							<?php echo_option("false","Count only usassigned issues","assigned"); ?>
							<?php echo_option("true","Count only assigned issues","assigned"); ?>
						</select>
						<p>
							Count only assigned or unassigned issues? Or all issues?
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
						New issues
					</div>
					<div class="form-field-holder">
						<input type="text" name="created_last_days"
							value="<?php echo_attr($_REQUEST["created_last_days"]); ?>"
						/>
						<p>
							Count only issues which were created within this many days.<br/>
							Should be a number, e.g. 7.
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
						Recently updated issues
					</div>
					<div class="form-field-holder">
						<input type="text" name="updated_last_days"
							value="<?php echo_attr($_REQUEST["updated_last_days"]); ?>"
						/>
						<p>
							Count only issues which were updated within this many days.<br/>
							Should be a number, e.g. 7.
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
						Recently closed issues
					</div>
					<div class="form-field-holder">
						<input type="text" name="closed_last_days"
							value="<?php echo_attr($_REQUEST["closed_last_days"]); ?>"
						/>
						<p>
							Count only issues which were closed within this many days.<br/>
							Should be a number, e.g. 7.
						</p>
					</div>
				</div>

				<div class="form-row">
					<div class="form-label">
					</div>
					<div class="form-field-holder">
						<input type="submit" value="Show Data"/>
					</div>
				</div>
				<input type="hidden" name="example" value="" id="example-field"/>
				<input type="hidden" name="showData" value="1"/>
			</form>
			<?php if (!$isExample) { ?>
				<a id="data"></a>
			<?php } ?>
			<h2>Data</h2>
			<?php if (isset($dataUrl)) { ?>
				<p>
					This is the Data URL you can put in dasheroo in order
					to have the configured KPI on your dashboard.
				</p>
				<div class="pre">
					<?php echo $dataUrl; ?>
				</div>
				<p>
					This is the data currently returned on this URL:
				</p>
				<pre class="pre"><?php echo $dataResponse; ?></pre>
			<?php } else { ?>
				<p>
					<i>No data to display currently, use the form above to create some.</i>
				</p>
			<?php } ?>

			<h2>Examples</h2>
			<p>
				So what can you use this tool for? These are a few examples of KPIs that
				can be useful for a development team. Obviously, these are just examples,
				if you want to use them you will need to change the labels used to the
				labels you use in your project.
			</p>
			<p>
				You can click on the links and
				populate the fields above, all fields except the projects field. Please note
				that if you do so, any previous information will be overwritten.
			</p>

			<p>
				<a href="" onclick="return submitExample('burndown')">
					Burndown chart
				</a><br/>
				This example measures the currently open issues which has the
				label <i>current-sprint</i>. If you plot for a few days it will
				give you a nice burndown chart that hopefully should point
				to your sprint deadline.
			</p>

			<p>
				<a href="" onclick="return submitExample('velocity')">
					Velocity
				</a><br/>
				This example measures the issues which were closed during
				the last seven days and that has the <i>resolved</i>
				label on them. This gives you a measurement of you teams
				weekly velocity, i.e. how many issues the team is currently
				able to deal with per week.
			</p>

			<p>
				<a href="" onclick="return submitExample('unassigned')">
					Unassigned open issues
				</a><br/>
				This example measures the number of unassigned open issues.
				This value should be as low as possible, since it means that 
				the team has stepped up and accepted responsibility for the
				open issues.
			</p>

			<h2>About</h2>
			<p>
				This tool is open source and lives on
				<a href="https://github.com/tunapanda/dasheroo-github-kpis">GitHub</a>.
			</p>
			<p>
				It was developed by 
				<a href="http://www.tunapanda.org/">Tunapanda Institute</a> as 
				a response to our internal needs to measure and visualize 
				software development KPIs.
			</p>
		</div>
		<div class="footer">
			<div class="footer-img">
				<a href="http://www.tunapanda.org/">
					<img src="img/cropped-tunapandalogo.png" style="width: 77px; height: 60px"/>
				</a>
			</div>
			<div class="footer-info">
				<a href="http://www.tunapanda.org/">Tunapanda Institute</a>
				exists to spread dignity, respect, and freedom through 
				learning for creative problem solving.
				<a href="http://www.tunapanda.org/">Tunapanda Institute</a> builds skills in 
				digital technology, collaboration, problem solving, and creative self-expression.
				<a href="http://www.tunapanda.org/">Tunapanda Institute</a> was founded in
				Kibera, Nairobi, Kenya, where it runs a centre for
				education, development, and research.
			</div>
			<div class="footer-right">
				<a href="https://co.clickandpledge.com/sp/d1/default.aspx?wid=93195" target="_blank"><img class="aligncenter" style="width: 210px; height: 34px; border: 1px solid #efefef;" title="Online donation system by ClickandPledge" src="https://s3.amazonaws.com/clickandpledge/Images/flair/buttons/210x34/CP_EN_GR_S_001.gif" alt="Online donation system by ClickandPledge" width="210" height="100" border="0" /></a>
			</div>
		</div>
	</body>
</html>