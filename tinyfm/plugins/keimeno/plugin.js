

var keicallb = {
    defaults: [['server', '/']],
    options: {},
    server: '',
    request: function(url) {
       console.log(url);
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            async:false,
            cache: false,
            success: function(data) { 
                fm.options.data = data;         
            },
        });
       
    },
    on_upload: function(file,ident) {
        var url = this.server+'cmd=on_upload&file='+file+'&ident='+ident;
        this.request(url);   
    },
    on_delete: function( ident) {
        var url = this.server+'cmd=on_delete&ident='+ident;
        this.request(url);   
    },
    on_rename: function( ident, new_ident, file) {
        var url = this.server+'cmd=on_rename&ident='+ident+'&new_ident='+new_ident+'&file='+file;
        this.request(url);   
    },
    on_fm_delete_request: function( hashes, $result) {
        var url = this.server+'cmd=on_fm_delete_request&hashes='+hashes;
        this.request(url);   
    },
    init: function() {
        console.log(_kei);
        var opt = $.merge(this.defaults,_kei);
        this.options = Object.fromEntries( opt);
        this.server = this.options.url+'admin/run.php?epage=fm.inc&';
        var kcm = this;
    }
}

jQuery(function() {
    console.log('init keicallb');
  var _opt = window._opt = window._opt || [];
  _opt.push(['data', null],['functions', [ ['on_upload', 'keicallb.on_upload'],['on_delete', 'keicallb.on_delete'],['on_fm_delete_request', 'keicallb.on_fm_delete_request'],['on_rename', 'keicallb.on_rename']]   ]);    
  fm.update_options(_opt);

  var _kei = window._kei = window._kei || [];
  _kei.push(['url', fm.options.url]);  
  keicallb.init();
});