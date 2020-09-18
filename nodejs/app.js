var createError = require('http-errors');
var express = require('express');
var path = require('path');
var cookieParser = require('cookie-parser');
var cookieSession = require('cookie-session');
//var session = require('express-session'); 支持redis、mongodb等第三方存储方式，详见express官网 - 资源 - 中间件
var logger = require('morgan');

var indexRouter = require('./routes/index');
var usersRouter = require('./routes/users');

var fs = require('fs');
var ini = require('ini');
var config = ini.parse(fs.readFileSync('.env', 'utf-8'));


process.env.NODE_ENV = config.app.env;
process.env.PORT = config.app.port;

var app = express();
config.mysql['password'] = config.mysql['pass'];
config.mysql['database'] = config.mysql['db'];
global.config = config

// view engine setup
app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'ejs');

app.use(logger('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

// 配置session
app.use(cookieSession({
  name: 'session_token',
  keys: ['abc'],

  // Cookie Options
  maxAge: 24 * 60 * 60 * 1000 // 24 hours
}))

// 验证身份
app.use(function(req, res, next) {
	let name = req.session.name;
	if (name == 'xxx') {
		next();
	} else {
		req.session.name = 'xxx';
		res.status(401);
		res.json({});
	}
});

app.use('/', indexRouter);
app.use('/users', usersRouter);

// catch 404 and forward to error handler
app.use(function(req, res, next) {
  next(createError(404));
});

// error handler
app.use(function(err, req, res, next) {
  // set locals, only providing error in development
  res.locals.message = err.message;
  res.locals.error = req.app.get('env') === 'local' ? err : {};

  // render the error page
  res.status(err.status || 500);
  res.render('error');
});

module.exports = app;
