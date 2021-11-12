module.exports = {
    plugins: [
        "@nextcloud"
    ],
    rules: {
        "@nextcloud/no-deprecations": "warn",
        "@nextcloud/no-removed-apis": "error"
    },
    globals: {
        t: false,
        n: false,
        OC: false,
        OCA: false,
        OCP: false
    },
    parser: "@babel/eslint-parser",
    parserOptions: {
        ecmaVersion: 6,
        requireConfigFile: false
    }
}