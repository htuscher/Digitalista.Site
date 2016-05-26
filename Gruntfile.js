// Generated on 2015-04-07 using
// generator-webapp 0.5.1
'use strict';

// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// If you want to recursively match all subfolders, use:
// 'test/spec/**/*.js'

module.exports = function (grunt) {

	// Time how long tasks take. Can help when optimizing build times
	require('time-grunt')(grunt);

	// Load grunt tasks automatically
	require('load-grunt-tasks')(grunt);

	// Configurable paths
	var config = {
		resPrivate: '../../Application/Jhoechtl.Digitalista.Design/Resources/Private',
		resPublic: 'Resources/Public'
	};

	// Define the configuration for all the tasks
	grunt.initConfig({

		// Project settings
		config: config,

		// Watches files for changes and runs tasks based on the changed files
		watch: {
			bower: {
				files: ['bower.json'],
				tasks: ['wiredep']
			},
			js: {
				files: ['<%= config.resPrivate %>/JavaScript/**/*.js'],
				tasks: ['jshint']
			},
			gruntfile: {
				files: ['Gruntfile.js']
			},
			sass: {
				files: ['<%= config.resPrivate %>/Styles/**/*.{scss,sass}'],
				tasks: ['sass:server', 'autoprefixer']
			},
			styles: {
				files: ['<%= config.resPrivate %>/Styles/**/*.css'],
				tasks: ['newer:copy:styles', 'autoprefixer']
			}
		},

		// Empties folders to start fresh
		clean: {
			dist: {
				files: [{
					dot: true,
					src: [
						'.tmp',
						'.sass-cache',
						'<%= config.resPublic %>/Styles',
						'<%= config.resPublic %>/Fonts',
						'<%= config.resPublic %>/JavaScript'
					]
				}]
			},
			server: '.tmp'
		},

		// Make sure code styles are up to par and there are no obvious mistakes
		jshint: {
			options: {
				jshintrc: '.jshintrc',
				reporter: require('jshint-stylish')
			},
			all: [
				'Gruntfile.js',
				'<%= config.resPrivate %>/JavaScript/{,*/}*.js',
				'!<%= config.resPrivate %>/JavaScript/vendor/*'
			]
		},

		// Compiles Sass to CSS and generates necessary files if requested
		sass: {
			options: {
				loadPath: '<%= config.resPrivate %>/Libraries'
			},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= config.resPrivate %>/Styles',
					src: ['*.{scss,sass}'],
					dest: '.tmp/Styles',
					ext: '.css'
				}]
			},
			server: {
				files: [{
					expand: true,
					cwd: '<%= config.resPrivate %>/Styles',
					src: ['*.{scss,sass}'],
					dest: '.tmp/Styles',
					ext: '.css'
				}]
			}
		},

		// Add vendor prefixed styles
		autoprefixer: {
			options: {
				browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
			},
			dist: {
				files: [{
					expand: true,
					cwd: '.tmp/Styles/',
					src: '{,*/}*.css',
					dest: '.tmp/Styles/'
				}]
			}
		},

		// Automatically inject Bower components into the HTML file
		wiredep: {
			app: {
				//ignorePath: /^\/|(\.\.\/){1,2}/,
				src: ['Resources/Private/Templates/Page/Default.html'],
				exclude: [
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/affix.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/alert.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/button.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/carousel.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/scrollspy.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/tooltip.js',
					'<%= config.resPrivate %>/Libraries/bootstrap-sass-official/assets/javascripts/bootstrap/popover.js'
				]
			},
			sass: {
				src: ['<%= config.resPrivate %>/Styles/{,*/}*.{scss,sass}'],
				ignorePath: /(\.\.\/){1,2}Libraries\//
			}
		},

		// Reads HTML for usemin blocks to enable smart builds that automatically
		// concat, minify and revision files. Creates configurations in memory so
		// additional tasks can operate on them
		useminPrepare: {
			options: {
				dest: '<%= config.resPublic %>'
			},
			html: 'Resources/Private/Templates/Page/Default.html'
		},

		// Performs rewrites based on rev and the useminPrepare configuration
		usemin: {
			options: {
				assetsDirs: [
					'<%= config.resPublic %>',
					'<%= config.resPublic %>/Fonts',
					'<%= config.resPublic %>/Images',
					'<%= config.resPublic %>/JavaScript',
					'<%= config.resPublic %>/Styles'
				]
			},
			//html: '<%= config.resPrivate %>/Templates/Page/Default.html',
			css: ['<%= config.resPublic %>/Styles/{,*/}*.css']
		},

		// The following *-min tasks produce minified files in the dist folder
		imagemin: {
			dist: {
				files: [{
					expand: true,
					cwd: '<%= config.resPrivate %>/Images',
					src: '{,*/}*.{gif,jpeg,jpg,png}',
					dest: '<%= config.resPublic %>/Images'
				}]
			}
		},

		// Copies remaining files to places other tasks can use
		copy: {
			dist: {
				files: [{
					expand: true,
					flatten: true,
					dot: true,
					cwd: '<%= config.resPrivate %>',
					src: 'Fonts/{,*/}*.*',
					dest: '<%= config.resPublic %>/Fonts'
				}, {
					expand: true,
					flatten: true,
					dot: true,
					cwd: '<%= config.resPrivate %>/Libraries/fontawesome',
					src: 'fonts/*.*',
					dest: '<%= config.resPublic %>/Fonts'
				},{
					expand: true,
					flatten: false,
					dot: true,
					cwd: '<%= config.resPrivate %>/JavaScript',
					src: 'jbcore/**/*.*',
					dest: '<%= config.resPublic %>/JavaScript'
				}]
			},
			styles: {
				expand: true,
				dot: true,
				cwd: '<%= config.resPrivate %>/Styles',
				dest: '.tmp/Styles/',
				src: '{,*/}*.css'
			}
		},

		// Run some tasks in parallel to speed up build process
		concurrent: {
			server: [
				'sass:server',
				'copy:styles'
			],
			dist: [
				'sass',
				'copy:styles',
				'imagemin'
			]
		}
	});


	grunt.registerTask('build', [
		'clean:dist',
		'wiredep',
		'useminPrepare',
		'concurrent:dist',
		'autoprefixer',
		'concat',
		'cssmin',
		'uglify',
		'copy:dist',
		'usemin'
	]);

	grunt.registerTask('default', [
		'newer:jshint',
		'build'
	]);
};
