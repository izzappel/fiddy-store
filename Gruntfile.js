module.exports = function(grunt) {

	grunt.initConfig({
		copy: {
		  main: {
		    files: [
		      // includes files within path
		      {expand: true, src: ['fiddystore/*'], dest: '/Applications/XAMPP/xamppfiles/htdocs/FiddyStore/prestashop/modules/'},
		    ]
		  }
		}
	});
	
	grunt.loadNpmTasks('grunt-contrib-copy');
	
	grunt.registerTask('deploy', ['copy']);
}
