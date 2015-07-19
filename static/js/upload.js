$('.plupload').each(function() {
	var ele = this;
	var ccuploader = new plupload.Uploader({
		runtimes : 'html5,flash,silverlight,html4',
		browse_button : ele,
		url : sUploadUrl,
		flash_swf_url : static_url + '/plupload/Moxie.swf',
		silverlight_xap_url : static_url + '/plupload/Moxie.xap',
		filters : {
			max_file_size : '2mb',
			mime_types: [
				{title : "Image files", extensions : "jpg,jpeg,gif,png"}
			]
		},
		init: {
			FilesAdded: function(up, files) {
				ccuploader.start();
			},
			FileUploaded: function(up, file, ret) {
				eval('var tmp=' + ret.response);
				if (tmp.iError != 0) {
					alert(tmp.msg);
					return true;
				}
				var file = tmp.file.sKey + '.' + tmp.file.sExt;
				if ($(ele).data('target')) {
					$($(ele).data('target')).val(file);
				}
				if ($(ele).data('img')) {
					var height = $(ele).data('height') || 0;
					var width = $(ele).data('width') || 0;
					$($(ele).data('img')).attr('src', getDFSViewURL(file,width,height));
				}
				if ($(ele).data('callback')) {
					eval($(ele).data('callback') + '(\'' + file + '\','+tmp.file.iWidth+', '+tmp.file.iHeight+')');
				}
			}
		}
	});
	ccuploader.init();
});

function getDFSViewURL(p_sFileKey, p_iWidth, p_iHeight, p_sOption, p_biz) {
    if(!p_sFileKey) {
        return '';
    }
    p_iWidth = p_iWidth || 0;
    p_iHeight = p_iHeight || 0;
    p_sOption = p_sOption || '';
	p_biz = p_biz || '';

	if (p_biz == 'banner') {
		sDfsViewUrl += '/fjbanner';
	}
    var tmp = p_sFileKey.split('.');
    var p_sKey = tmp[0];
    var p_sExt = tmp[1];
    if(0 == p_iWidth && 0 == p_iHeight){
        return sDfsViewUrl + '/' + p_sKey + '.' + p_sExt;
    }else{
        if('' == p_sOption) {
            return sDfsViewUrl + '/' + p_sKey + '/' + p_iWidth + 'x' + p_iHeight + '+' + p_sExt;
        }else{
            return sDfsViewUrl + '/' + p_sKey + '/' + p_iWidth + 'x' + p_iHeight + '_' + p_sOption + '.' + p_sExt;
        }
    }
}