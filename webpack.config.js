var ExtractTextPlugin = require("extract-text-webpack-plugin");
module.exports = {
    entry: "./entry.js",
    output: {
        path: __dirname,
        filename: "web/vendor-scripts.js"
    },
    module: {
        loaders: [
            { test: /\.css$/, loader: "style-loader!css-loader" }
        ]
    },
    plugins: [
        new ExtractTextPlugin("styles.css")
    ]
};

