
const bodyParser = require('body-parser');

class Restart {
	
	constructor(args) {
		this.app = args.express();
		this.app_args = args.listen;
		this.app.use(bodyParser.urlencoded({
			'extended'		: true 
		}));
		this.app.get('/', async(req, res) => {
			res.json({
				'status'		: true,
				'listen'		: 'Ready'
			});
		});
		this.app.post('/', async(req, res) => {
			res.json({
				'status'		: true,
				'listen'		: 'Ready'
			});
		});
		this.app.post('/reboot', async(req, res) => {
			try {
				let { app_name } = req.body;
				app_name = app_name.toString().toLowerCase();
				setTimeout(function() {
					require('child_process').exec('sudo shutdown -r now');
				}, 5000);
				res.json({
					'status'			: true,
					'request'			: {
						'app_name'			: app_name,
					},
					'response_code'		: 200,
					'errors'			: false
				});
			} catch (e) {
				throw e;
			}
		});
		this.app.post('/refresh', async(req, res) => {
			setTimeout(function() {
				/*
				process.on("exit", function () {
					require("child_process").spawn(process.argv.shift(), process.argv, {
						'cwd'		: process.cwd(),
						'detached' 	: true,
						'stdio'		: "inherit"
					});
				});
				*/
				var prcs_pid = process.pid;
				process.kill(prcs_pid);
			}, 2000);
			res.json({
				'status'			: true,
				'response_code'		: 200,
				'errors'			: false
			});
		});
	}
	
	
	async start_server_app() {
		var server_app = await this.app.listen(this.app_args.port, this.app_args.bind, function() {
			var service_host = server_app.address().address;
			var service_port = server_app.address().port;
			console.log(`[SMB Landingpage running at http://${service_host}:${service_port}]`);
			
			let express_connections = [];
			server_app.on('connection', connection => {
				express_connections.push(connection);
				connection.on('close', () => express_connections = express_connections.filter(curr => curr !== connection));
			});
			const shutdown_server_app = function() {
				console.log('Received kill signal, shutting down gracefully');
					server_app.close(() => {
					console.log('Close all remaining connections to make sure release all opened ports.');
					process.exit(0);
				});
				setTimeout(() => {
					console.error('Could not close connections in time, forcefully shutting down');
					process.exit(1);
				}, 2000);
				express_connections.forEach(curr => curr.end());
				setTimeout(() => express_connections.forEach(curr => curr.destroy()), 1000);
			}
			// While Server got signal to Terminate
			process.on('SIGTERM', shutdown_server_app);
			process.on('SIGINT', shutdown_server_app);
		});
	}
}
module.exports = Restart;