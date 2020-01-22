// summary r.20200121
function smartHandleDocSelector(obj) {
	if(!obj) {
		return;
	}
	var selection = '';
	try {
		selection = obj.options[obj.selectedIndex].value;
	} catch(err){}
	if(selection) {
		self.location.hash = String(selection);
		obj.selectedIndex = 0; // reset selection to index zero
	} else {
		selection = '';
		//self.location.href = self.location.href.split('#')[0]; // because reset selection to index zero avoid refresh !
	}
}