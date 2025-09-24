module.exports = {
	extends: [ '@wordpress/stylelint-config' ],
	rules: {
		// Plugin-specific overrides
		'selector-class-pattern': null,
		'declaration-property-unit-allowed-list': null,
		'font-weight-notation': null,
		'comment-empty-line-before': null,
		'rule-empty-line-before': null,
		// Allow vendor prefixes for legacy browser support
		'property-no-vendor-prefix': null,
		'value-no-vendor-prefix': null
	},
	ignoreFiles: [
		'build/**/*',
		'vendor/**/*',
		'node_modules/**/*',
		'assets/css/datatables*.css'
	]
};
