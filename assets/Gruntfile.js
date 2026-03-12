module.exports = function(grunt) {

    "use strict";

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        sg: {

            assets_dir:        'assets',
            dist_dir:          'dist'

        },

        sass: {
            options: {
                sourceMap: true
            },
            dist: {
                files: {

                    // Bootstrap themes
                    //
                    '<%= sg.dist_dir %>/main.css': '<%= sg.assets_dir %>/theme/main.scss'
                }
            }
        },
        cssmin: {
            options: {
                shorthandCompacting: false,
                    roundingPrecision: -1
            },
            target: {
                files: {
                    '<%= sg.dist_dir %>/main.min.css': ['<%= sg.dist_dir %>/main.css']
                }
            }
        },
        fontello: {
            create: {
                options: {
                    config: '<%= sg.assets_dir %>/components/icons/config.json',
                    fonts: '<%= sg.assets_dir %>/components/icons/fonts',
                    styles: '<%= sg.assets_dir %>/components/icons/scss',
                    scss: true,
                    force: true,
                    exclude: [
                        'animation.css',
                        'fontello.css',
                        'fontello-embedded.css',
                        'fontello-ie7.css',
                        'fontello-ie7-codes.css',
                        'fontello-ie7-codes.css'
                    ]
                }
            }
        },
        copy: {
            fonts_icons: {
                files: [{
                    expand: true,
                    cwd: '<%= sg.assets_dir %>/components/icons/fonts',
                    src: ['**'],
                    dest: '<%= sg.dist_dir %>/fonts'
                }]
            }
        },
        watch: {
            css: {
                files: ['<%= sg.assets_dir %>/**/*.scss'],
                tasks: ['sass', 'cssmin'],
                options: {
                    livereload: false
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-fontello');

    grunt.registerTask('dev', [
        'sass', 'cssmin', 'fontello', 'copy', 'watch'
    ]);

};

