var args            = require('yargs').argv;
var bump            = require('gulp-bump');
var checktextdomain = require('gulp-checktextdomain');
var gulp            = require('gulp');
var potomo          = require('gulp-potomo');
var pseudo          = require('gulp-pseudo-i18n');
var pump            = require('pump');
var rename          = require('gulp-rename');
var runSequence     = require('run-sequence');
var sort            = require('gulp-sort');
var wpPot           = require('gulp-wp-pot');
var yaml            = require('yamljs');

var config = yaml.load('config.yml');

gulp.task('bump', function() {
	['patch', 'minor', 'major'].some(function(arg) {
		if(!args[arg])return;
		for(var key in config.bump) {
			if(!config.bump.hasOwnProperty(key))continue;
			pump([
				gulp.src(config.bump[key]),
				bump({type:arg,key:key}),
				gulp.dest('.'),
			]);
		}
	});
});

gulp.task('default', function() {
	return runSequence('po', 'potomo');
});

gulp.task('po', function() {
	return pump([
		gulp.src(config.watch.php),
		checktextdomain({
			text_domain: config.language.domain,
			keywords: [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'_ex:1,2c,3d',
				'_n:1,2,4d',
				'_nx:1,2,4c,5d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d',
			],
		}),
		sort(),
		wpPot({
			domain: config.language.domain,
			lastTranslator: config.language.translator,
			team: config.language.team,
		}),
		pseudo({
			charMap: {},
		}),
		rename(config.language.domain + '-en_US.po'),
		gulp.dest(config.dest.lang),
	]);
});

gulp.task('potomo', function() {
	return pump([
		gulp.src(config.dest.lang + '*.po'),
		potomo(),
		gulp.dest(config.dest.lang),
	]);
});
