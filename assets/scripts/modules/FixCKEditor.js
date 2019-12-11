class FixCKEditor {
	static init() {
		if (typeof CKEDITOR !== 'undefined') {
			for (const instance in CKEDITOR.instances) {
				CKEDITOR.instances[instance].on('change', function(e) {
					this.updateElement();
				})
			}
		}
	}
}

export default FixCKEditor