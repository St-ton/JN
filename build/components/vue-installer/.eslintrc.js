module.exports = {
    root:          true,
    parserOptions: {
        parser: 'babel-eslint'
    },
    env:           {
        browser: true,
    },
    // https://github.com/feross/standard/blob/master/RULES.md#javascript-standard-style
    extends:       [
        // https://github.com/vuejs/eslint-plugin-vue#priority-a-essential-error-prevention
        // consider switching to `plugin:vue/strongly-recommended` or `plugin:vue/recommended` for stricter rules.
        'plugin:vue/essential',
        'standard',
    ],
    // required to lint *.vue files
    plugins:       [
        'vue'
    ],
    // add your custom rules here
    'rules':       {
        // allow paren-less arrow functions
        'arrow-parens':                0,
        'space-before-blocks':         2,
        'space-before-function-paren': [2, 'never'],
        'space-in-parens':             2,
        'space-infix-ops':             2,
        'space-unary-ops':             2,
        'spaced-comment':              0,
        'indent':                      ['error', 4],
        'generator-star-spacing':      0,
        'semi':                        ['error', 'always'],
        'operator-linebreak':          ['error', 'before'],
        'no-debugger':                 process.env.NODE_ENV === 'production' ? 2 : 0,
        'key-spacing':                 [2, {
            'beforeColon': false,
            'afterColon':  true,
            'mode':        'minimum',
            'align':       'value'
        }]
    }
};
