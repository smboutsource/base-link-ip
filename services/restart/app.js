
const arguments = process.argv;
let env = 'dev';
if (arguments[2]) {
	env = arguments[2];
}

const express = require('express');
const restart = require('./restart.js');
const config = require('./config.js');





const app_envs = new Promise((resolve, reject) => {
	try {
		env = env.toString().toLowerCase();
		const app_config = new config({
			'env'			: env
		});
		const result = app_config.set_envs(env);
		resolve(result);
	} catch (e) {
		throw new Error(e);
	}
});


app_envs.then(function(data) {
	let environment = data.ini.environment.environment;
	
	let ResApp = new restart({
		'environment'	: environment,
		'express'		: express,
		'listen'		: {
			'bind'			: data.ini.servicesrestart['servicesrestart.' + environment + '.bind'],
			'port'			: data.ini.servicesrestart['servicesrestart.' + environment + '.port']
		}
	});
	ResApp.start_server_app();
});

