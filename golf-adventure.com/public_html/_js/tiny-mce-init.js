tinymce.init({
	selector: "textarea.editor",theme: "modern",width: 680,height: 300,
	entity_encoding : "raw",
	plugins: [
		"advlist autolink link image lists charmap print preview hr anchor pagebreak",
		"searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
		"table contextmenu directionality emoticons paste textcolor responsivefilemanager code"
	],
	content_css : "/_css/editor.css",
	image_dimensions: false,
	toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
	toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
	image_advtab: true ,
	external_filemanager_path:"/_filemanager/",
	filemanager_title:"Responsive Filemanager" ,
	external_plugins: { "filemanager" : "/_filemanager/plugin.min.js"}
});