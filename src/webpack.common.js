const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')

module.exports = {
	entry: {
		main: path.join(__dirname, 'main.js'),
		dashboard: path.join(__dirname, 'dashboard.js'),
	},
	output: {
		path: path.resolve(__dirname, '../js'),
		publicPath: '/js/',
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader'],
			},
			{
				test: /\.scss$/,
				use: ['style-loader', 'css-loader', 'sass-loader'],
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					hotReload: false, // disables Hot Reload. This adds an absolute path in the mapfile which causes issues.
				},
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
			{
				test: /\.(png|jpg|gif)$/,
				loader: 'file-loader',
				options: {
					name: '[name].[ext]?[hash]',
				},
			},
			{
				test: /\.(svg)$/i,
				use: [
					{
						loader: 'url-loader',
					},
				],
			},
		],
	},
	plugins: [new VueLoaderPlugin()],
	resolve: {
		extensions: ['*', '.js', '.vue'],
	},
}
