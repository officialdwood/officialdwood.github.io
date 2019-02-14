module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      build: {
        src: 'js/main.js',
        dest: 'js/build/<%= pkg.name %>.min.js'
      }
    },
    sass: {
      dist: {
        options: {
            style: 'compressed'
        },
        files: {
            'css/style.css': 'scss/style.scss'
        }
      } 
    },
    watch: {
      options: {
        livereload: true,
      },
      scripts: {
          files: ['js/*.js'],
          tasks: ['uglify'],
          options: {
              spawn: false,
          },
      },
      css: {
          files: ['scss/**/*.scss'],
          tasks: ['sass'],
          options: {
              spawn: false,
          }
      } 
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-sass');

  // Default task(s).
  grunt.registerTask('default', ['watch']);

};
