
// app.js # nodejs
// convert HTML to Markdown
// (c) 2021 unix-world.org

// HINT: if get the message: `Trace/BPT trap`, use the --jitless option to run nodejs

const TurndownService = require('../turndown/lib/turndown');
const turndownService = new TurndownService();
const turndownPluginGfm = require('../turndown/lib/turndown-plugin-gfm');
const gfm = turndownPluginGfm.gfm;
turndownService.use(gfm);

const fs = require('fs');


fs.access('db-md.json', fs.F_OK, (err) => {

	if(err) {
		console.log('CLEANUP: OK, db-md.json does not exist, NO CLEANUP IS NECESSARY ...');
		return true;
	}

	try {
		console.log('CLEANUP: Trying to DELETE db-md.json ...');
		fs.unlinkSync('db-md.json');
	} catch(err) {
		console.error('CLEANUP: FAILED to delete db-md.json ...', err);
		return false;
	}

	console.log('CLEANUP: OK, db-md.json was DELETED ...');
	return true;

});

const theJsonDbFile = 'db.json.optimized.json';
console.log('Processing DB File', theJsonDbFile);

fs.readFile(theJsonDbFile, null, (err, theSource) => {

	if(err) {
		console.error('READ JSON: FAILED to READ ' + theJsonDbFile + ' ...', err);
		return false;
	}

	let json = null;
	try {
		json = JSON.parse(theSource);
	} catch(err) {
		console.error('READ JSON: FAILED to PARSE ' + theJsonDbFile + ' ...', err);
		return false;
	}

	if((typeof(json) !== 'object') || (json === null)) {
		console.error('READ JSON: INVALID FORMAT / NOT ASSOCIATIVE ARRAY {OBJECT} # ' + theJsonDbFile + ' ...');
		return false;
	}

	let currentKey = null;
	let currentHTML = null;
	let markdown = null;
	let mDocs = {};
	let loops = 0;
	let convertedOk = 0;
	try {
		Object.keys(json).forEach(key => {
		//	console.log(process.memoryUsage());
			loops++;
			currentKey = String(key || '');
			currentHTML = String(json[key] || '');
			json[key] = null; // free memory
			markdown = null;
			if(!currentKey || !currentHTML) {
				console.warn('CONVERT JSON: SKIP: INVALID KEY OR INVALID HTML Data at key: `' + currentKey + '` # ' + theJsonDbFile + ' ...');
			} else {
				currentHTML = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' + currentHTML + '</body></html>';
				markdown = String(turndownService.turndown(currentHTML) || '');
				if(!markdown) {
					console.warn('CONVERT JSON: EMPTY MARKDOWN at key: `' + currentKey + '` # ' + theJsonDbFile + ' ...');
				} else {
					convertedOk++;
					console.log('CONVERT JSON: OK [PROCESSED] [' + loops + '/' + convertedOk + '] key: `' + currentKey + '` ; Markdown length is:', markdown.length);
					mDocs[currentKey] = markdown;
				}
			} //end if else
		});
	} catch(err) {
		console.error('CONVERT JSON: FAILED at key: `' + currentKey + '` # ' + theJsonDbFile + ' ...', err);
		return false;
	}

	try {
		mDocs = String(JSON.stringify(mDocs, null, 2) || '');
	} catch(err) {
		mDocs = '';
		console.error('CONVERT JSON: FAILED to compose JSON MARKDOWN # db-md.json ...', err);
		return false;
	}

	fs.writeFile('db-md.json', mDocs, (err) => {
		if(err) {
			console.error('WRITE MARKDOWN: FAILED to WRITE db-md.json ...', err);
			throw err;
			return false;
		}
	});
	console.log('WRITE MARKDOWN: DONE, SAVED as db-md.json ... OK');
	return true;

});

// #END
