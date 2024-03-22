
const path = require('path');
const fs = require('fs');
const ini = require('ini');

const ini_instance_path = path.join(path.dirname(path.dirname(__dirname)), 'config', 'main.ini');
if (!fs.existsSync(ini_instance_path)) {
	throw console.error('Not have main ini.');
	process.exit();
}
const ini_instance = ini.parse(fs.readFileSync(ini_instance_path, 'utf-8'));

class ServiceConf {
	constructor(args) {
		this.app_envcode = args.env.toString().toLowerCase();
		this.envs = {};
	}
	
	async set_envs(app_env) {
		try {
			this.envs['env'] = app_env;
			this.envs['ini'] = ini_instance;
			
			return this.envs;
		} catch (e) {
			throw e;
		}
	}
	async get_envs() {
		return this.envs;
	}
}

module.exports = ServiceConf;