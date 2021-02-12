const path = require('path')

module.exports = {
	entry: {
		settingsAdmin: path.join(__dirname, "src", "settings-admin.js")
	},
	output: {
		path: path.resolve(__dirname, "./js"),
		publicPath: "/js/",
		filename: "[name].js"
	}
}