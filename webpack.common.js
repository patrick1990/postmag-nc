const path = require('path')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')

module.exports = {
	entry: {
		settingsAdmin: path.join(__dirname, "src", "settingsAdmin.js"),
		aliasListHandler: path.join(__dirname, "src", "aliasListHandler.js")
	},
	output: {
		path: path.resolve(__dirname, "./js"),
		publicPath: "/js/",
		filename: "[name].js"
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				loader: "babel-loader",
				exclude: /node_modules\//
			},
			{
				test: /\.scss$/,
				use: ["style-loader", "css-loader", "sass-loader"]
			},
			{
				test: /\.(png|jpg|gif|svg)/,
				loader: "file-loader",
				options: {
					name: "[name].[ext]",
					outputPath: "../img",
					publicPath: "/apps/postmag/img/"
				}
			}
		]
	},
	plugins: [
		new CleanWebpackPlugin()
	],
	resolve: {
		extensions: ["*", ".js", ".json"]
	}
}