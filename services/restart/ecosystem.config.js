module.exports = {
	"apps": [
		{
		"name"						: "smb-link-ip-redirector",
		"script"					: "./app.js",
		"error_file"			: "/dev/null",
		"out_file"				: "/dev/null",
		"args"						: "dev",
		"log_date_format"	: "YYYY-MM-DD HH:mm:ss Z",
		"time"						: true
		}
	]
};