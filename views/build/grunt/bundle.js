module.exports = function(grunt) { 

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);

    /**
     * Remove bundled and bundling files
     */
    clean.taoltibundle = ['output',  root + '/taoLti/views/js/controllers.min.js'];
    
    /**
     * Compile tao files into a bundle 
     */
    requirejs.taoltibundle = {
        options: {
            baseUrl : '../js',
            dir : 'output',
            mainConfigFile : './config/requirejs.build.js',
            paths : { 'taoLti' : root + '/taoLti/views/js' },
            modules : [{
                name: 'taoLti/controller/routes',
                include : ext.getExtensionsControllers(['taoLti']),
                exclude : ['mathJax', 'mediaElement'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taoltibundle = {
        files: [
            { src: ['output/taoLti/controller/routes.js'],  dest: root + '/taoLti/views/js/controllers.min.js' },
            { src: ['output/taoLti/controller/routes.js.map'],  dest: root + '/taoLti/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taoltibundle', ['clean:taoltibundle', 'requirejs:taoltibundle', 'copy:taoltibundle']);
};
