/* jshint node:true */
module.exports = function(grunt) {
	var SOURCE_DIR = '',
		BUILD_DIR = 'build/',
		CURRENT_TIME = new Date(),
		CURRENT_YEAR = CURRENT_TIME.getFullYear(),

		BBP_ACT_EXCLUDED_MISC = [
			'!**/.idea/**',
			'!**/bin/**',
			'!**/build/**',
			'!**/coverage/**',
			'!**/node_modules/**',
			'!**/tests/**',
			'!Gruntfile.js*',
			'!package.json*',
			'!phpunit.xml*',
			'!.{editorconfig,gitignore,jshintrc,travis.yml,DS_Store}'
		];

	// Load tasks.
	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		checktextdomain: {
			options: {
				text_domain: 'tba-bbp-auto-close-topics',
				correct_domain: false,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [ '**/*.php' ].concat( BBP_ACT_EXCLUDED_MISC ),
				expand: true
			}
		},
		clean: {
			all: [ BUILD_DIR ],
			dynamic: {
				cwd: BUILD_DIR,
				dot: true,
				expand: true,
				src: []
			}
		},
		copy: {
			files: {
				files: [
					{
						cwd: '',
						dest: 'build/',
						dot: true,
						expand: true,
						src: ['**', '!**/.{svn,git}/**'].concat( BBP_ACT_EXCLUDED_MISC )
					}
				]
			}
		},
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: ['Gruntfile.js']
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'bbp-auto-close-topics.php',
					potComments: 'Copyright (C) ' + CURRENT_YEAR + ' Brandon Allen\nThis file is distributed under the same license as the BBP Auto-Close Topics package.\nSend translations to <wp_plugins [at] brandonallen (dot) org>.',
					potFilename: 'bbp-auto-close-topics.pot',
					processPot: function( pot ) {
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/thebrandonallen/bbp-auto-close-topics';
						pot.headers['last-translator'] = 'BRANDON ALLEN <wp_plugins@brandonallen.me>';
						pot.headers['language-team'] = 'ENGLISH <wp_plugins@brandonallen.me>';
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		},
		phpunit: {
			'default': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist']
			},
			multisite: {
				cmd: 'phpunit',
				args: ['-c', 'tests/phpunit/multisite.xml']
			}
		},
		'string-replace': {
			dev: {
				files: {
					'bbp-auto-close-topics.php': 'bbp-auto-close-topics.php',
				},
				options: {
					replacements: [{
						pattern: /(\$this->version.*)'(.*)';/gm, // For plugin version variable
						replacement: '$1\'<%= pkg.version %>\';'
					},
					{
						pattern: /(\* Version:\s*)(.*)$/gm, // For plugin header
						replacement: '$1<%= pkg.version %>'
					}]
				}
			},
			build: {
				files: {
					'bbp-auto-close-topics.php': 'bbp-auto-close-topics.php',
					'readme.md': 'readme.md',
					'readme.txt': 'readme.txt'
				},
				options: {
					replacements: [{
						pattern: /(\$this->version.*)'(.*)';/gm, // For plugin version variable
						replacement: '$1\'<%= pkg.version %>\';'
					},
					{
						pattern: /(\* Version:\s*)(.*)$/gm, // For plugin header
						replacement: '$1<%= pkg.version %>'
					},
					{
						pattern: /(Stable tag:[\*\ ]*)(.*\S)/gim, // For readme.*
						replacement: '$1<%= pkg.version %>'
					}]
				}
			}
		},
		watch: {
			js: {
				files: ['Gruntfile.js'],
				tasks: ['jshint']
			}
		}
	});

	// Build tasks.
	grunt.registerTask( 'build', [ 'checktextdomain', 'string-replace:build', 'makepot', 'copy:files' ] );

	// PHPUnit test task.
	grunt.registerMultiTask( 'phpunit', 'Runs PHPUnit tests, including the multisite tests.', function() {
		grunt.util.spawn( {
			cmd: this.data.cmd,
			args: this.data.args,
			opts: { stdio: 'inherit' }
		}, this.async() );
	} );

	// Register the default tasks.
	grunt.registerTask('default', ['watch']);

};
