module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
		
		// compress multiple files into a single file
       
		//less preprocessing
		less: {
			development: {
		    options: {
		      paths: ["css/"]
		    },
		    files: {
		      "css/result.css": "css/style.less"
		    }
		  }
    	},
		
		// watch for changes and complete as requested
		watch: {
			options: {
				livereload: true,
			},
		    scripts: {
		        files: ['js/*.js'],
		        tasks: ['concat', 'uglify'],
		        options: {
		            spawn: false,
		        },
		    },
		    scripts: {
		        files: ['css/*.css'],
		        tasks: ['concat', 'uglify'],
		        options: {
		            spawn: false,
		        },
		    },
		    css: {
			    files: ['css/*.less'],
			    tasks: ['less'],
			    options: {
			        spawn: false,
			    }
			} 
		}

    });

    // 3. Where we tell Grunt we plan to use this plug-in.
 
    grunt.loadNpmTasks('grunt-contrib-less');    
    grunt.loadNpmTasks('grunt-contrib-watch');


    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    //grunt.registerTask('default', ['concat','uglify','imagemin','pngmin','smushit','less','watch']);
    grunt.registerTask('default', ['less','watch']);


};