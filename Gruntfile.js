module.exports = function(grunt) {

	grunt.initConfig({
		copy: {
		  main: {
		    files: [
		      // includes files within path
		      {expand: true, src: ['prestashop/**'], dest: '/Applications/XAMPP/xamppfiles/htdocs/FiddyStore/'},
		    ]
		  }
		}
	});
	
	grunt.loadNpmTasks('grunt-contrib-copy');
	
	grunt.registerTask('deploy', ['copy']);
}
