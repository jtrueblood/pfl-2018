module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
		
		// compress multiple files into a single file
        concat: {
            dist: {
		        src: [
		            'js/*.js', // All .js in the js folder
		        ],
		        dest: 'js/build/production.js',
		    },
		    dist: {
		        src: [
		            'css/*.css', // All styles in the css folder
		        ],
		        dest: 'css/build/production.css',
		    }
        },
        
        // compress files to single line
        uglify: {
			build: {
				src: 'js/build/production.js',
				dest: 'js/build/production.min.js'
			},
		},
		
		// minify images in img folder for design files and for style background imgages
		imagemin: {
		    dynamic: {
		        files: [{
		            expand: true,
		            cwd: 'img/',
		            src: ['*.{png,jpg,gif}'],
		            dest: 'img/build/'
		        }]
		    }
		},
		
		//minify images in uploads folder
		pngmin: {
		
			compile: {
				options: {
					ext: '.png',
					force: true
				},
			
					files: [{
						expand: true, // required option
						src: ['**/*.png'],
						cwd: '../../uploads/', // required option
						dest: '../../uploads/'
					}]
				}
			},
	
		smushit: {
			mygroup: {
				src: ['../../uploads/**/*.jpg', '../../uploads/**/*.png'],
				dest: '../../uploads/'
			}
		},
		
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
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-pngmin');
	//grunt.loadNpmTasks('grunt-smushit');
    grunt.loadNpmTasks('grunt-contrib-less');    
    grunt.loadNpmTasks('grunt-contrib-watch');


    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    //grunt.registerTask('default', ['concat','uglify','imagemin','pngmin','smushit','less','watch']);
    grunt.registerTask('default', ['concat','uglify','imagemin','pngmin','less','watch']);

};