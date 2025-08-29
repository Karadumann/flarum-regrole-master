const config = require('flarum-webpack-config');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

module.exports = (env, argv) => {
  const webpackConfig = config({
    useExtensions: ['karadumann-advanced-registration-roles']
  });

  // Add bundle analyzer in analyze mode
  if (process.env.ANALYZER) {
    webpackConfig.plugins.push(
      new BundleAnalyzerPlugin({
        analyzerMode: 'static',
        openAnalyzer: false,
        reportFilename: 'bundle-report.html'
      })
    );
  }

  // Development optimizations
  if (argv.mode === 'development') {
    webpackConfig.devtool = 'eval-source-map';
    webpackConfig.optimization = {
      ...webpackConfig.optimization,
      minimize: false
    };
  }

  // Production optimizations
  if (argv.mode === 'production') {
    webpackConfig.optimization = {
      ...webpackConfig.optimization,
      usedExports: true,
      sideEffects: false
    };
  }

  return webpackConfig;
};