<?php ob_start(); ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
    jQuery.fn.getPath = function () {
	if (this.length != 1)
	    throw 'Requires one element.';

	var path, node = this;
	while (node.length) {
	    var realNode = node[0], name = realNode.localName;
	    if (!name)
		break;
	    name = name.toLowerCase();
	    var parent = node.parent();
	    var siblings = parent.children(name);
	    if (siblings.length > 1 && siblings.index(realNode) > 0) {
//            name += '[' + siblings.index(realNode) + ']';
		name += ':eq(' + siblings.index(realNode) + ')';
	    }

	    path = name + (path ? '>' + path : '');
	    node = parent;
	}

	return path;
    };
    $("*").click(function () {
	return false;
    });
    htiTagSelector = "";
    htiTagHtml = "";
    $("<?php echo $contentSelector; ?> *").click(function () {
	var $selector = $(this).getPath();
	htiTagSelector = $selector;
	htiTagHtml = $(this)[0].outerHTML;
//    $.ajax({
//	url: "' . Url::to(['integrator/include', 'folder' => $folderName, 'file' => $file]) . '&selector=" + $selector,
//    }).done(function(result) {
//	alert(result);
//    });
	return false;
    });
    $("<?php echo $contentSelector; ?> *").hover(function () {

	var position = $(this).offset();
	$(".HtmlTemplateIntegrator").css("width", $(this).outerWidth());
	$(".HtmlTemplateIntegrator").css("height", $(this).outerHeight());
	$(".HtmlTemplateIntegrator").css("left", position.left);
	$(".HtmlTemplateIntegrator").css("top", position.top);
	$(".HtmlTemplateIntegrator").show();
//    $(this).css("-webkit-box-shadow","0px 0px 5px 0px rgba(255, 0, 0, 0.75)");
//    $(this).css("-moz-box-shadow","0px 0px 5px 0px rgba(255, 0, 0, 0.75)");
//    $(this).css("box-shadow","0px 0px 5px 0px rgba(255, 0, 0, 0.75)");
    }, function () {
	$(".HtmlTemplateIntegrator").hide();
//    $(this).css("-webkit-box-shadow","none");
//    $(this).css("-moz-box-shadow","none");
//    $(this).css("box-shadow","none");
    });
</script>
<style>

    .htmlintegratorResetStyles {
	z-index: 99999999999 !important;
	//    top: 0 !important;
	//    left: 0 !important;
	display: block !important;
	border: 0 none !important;
	margin: 0 !important;
	padding: 0 !important;
	outline: 0 !important;
	min-width: 0 !important;
	max-width: none !important;
	min-height: 0 !important;
	max-height: none !important;
	position: absolute !important;
	transform: rotate(0deg) !important;
	transform-origin: 50% 50% !important;
	border-radius: 0 !important;
	box-shadow: none !important;
	background: transparent none !important;
	pointer-events: none !important;
	white-space: normal !important;
    }
</style>
<div class="HtmlTemplateIntegrator htmlintegratorResetStyles" style="-webkit-box-shadow:0px 0px 2px 2px #39F !important;-moz-box-shadow:0px 0px 2px 2px #39F !important;box-shadow:0px 0px 2px 2px #39F !important;"></div>
</body>
<?php
$js = ob_get_contents();
ob_end_clean();