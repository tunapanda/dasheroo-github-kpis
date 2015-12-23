module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-ftpuploadtask');

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		ftpUploadTask: {
			limikael_altervista_org: {
				options: {
					user: "limikael",
					password: process.env.ALTERVISTA_PASSWORD,
					host: "ftp.limikael.altervista.org",
					checksumfile: "_checksums/dasheroo-github-kpis.json"
				},

				files: [{
					expand: true,
					dest: "dasheroo-github-kpis",
					src: ["**","!node_modules/**",".githubuserpwd"]
				}]
			},
		}
	});
}