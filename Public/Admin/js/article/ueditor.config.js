/**
 * 在线编辑器配置
 */
//具体参数配置在  editor_config.js  中
var options = {
    zIndex: 999,
    initialFrameWidth: "50%", //初化宽度
    initialFrameHeight: 300, //初化高度
    focus: false, //初始化时，是否让编辑器获得焦点true或false
    maximumWords: 99999, removeFormatAttributes: 'class,style,lang,width,height,align,hspace,valign'
    , //允许的最大字符数 'fullscreen',
    pasteplain:false, //是否默认为纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴
    autoHeightEnabled: true
 /*   autotypeset: {
        mergeEmptyline: true,        //合并空行
        removeClass: true,           //去掉冗余的class
        removeEmptyline: false,      //去掉空行
        textAlign: "left",           //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
        imageBlockLine: 'center',    //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
        pasteFilter: false,          //根据规则过滤没事粘贴进来的内容
        clearFontSize: false,        //去掉所有的内嵌字号，使用编辑器默认的字号
        clearFontFamily: false,      //去掉所有的内嵌字体，使用编辑器默认的字体
        removeEmptyNode: false,      //去掉空节点
                                     //可以去掉的标签
        removeTagNames: {"font": 1},
        indent: false,               // 行首缩进
        indentValue: '0em'           //行首缩进的大小
    }*/,
	toolbars: [
           ['fullscreen', 'source', '|', 'undo', 'redo',
               '|', 'bold', 'italic', 'underline', 'fontborder',
               'strikethrough', 'superscript', 'subscript',
               'removeformat', 'formatmatch', 'autotypeset',
               'blockquote', 'pasteplain', '|', 'forecolor',
               'backcolor', 'insertorderedlist',
               'insertunorderedlist', 'selectall', 'cleardoc', '|',
               'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
               'customstyle', 'paragraph', 'fontfamily', 'fontsize',
               '|', 'directionalityltr', 'directionalityrtl',
               'indent', '|', 'justifyleft', 'justifycenter',
               'justifyright', 'justifyjustify', '|', 'touppercase',
               'tolowercase', '|', 'link', 'unlink', 'anchor', '|',
               'imagenone', 'imageleft', 'imageright', 'imagecenter',
               '|', 'insertimage', 'emotion', 'insertvideo',
               'attachment', 'map', 'gmap', 'insertframe',
               'insertcode', 'webapp', 'pagebreak', 'template',
               'background', '|', 'horizontal', 'date', 'time',
               'spechars', 'wordimage', '|',
               'inserttable', 'deletetable',
               'insertparagraphbeforetable', 'insertrow', 'deleterow',
               'insertcol', 'deletecol', 'mergecells', 'mergeright',
               'mergedown', 'splittocells', 'splittorows',
               'splittocols', '|', 'print', 'preview', 'searchreplace']
       ],
    /* 上传图片配置项 */
    "imageActionName": "uploadimage", /* 执行上传图片的action名称 */
    "imageFieldName": "upfile", /* 提交的图片表单名称 */
    "imageMaxSize": 2048000, /* 上传大小限制，单位B */
    "imageAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
    "imageCompressEnable": true, /* 是否压缩图片,默认是true */
    "imageCompressBorder": 1600, /* 图片压缩最长边限制 */
    "imageInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageUrlPrefix": "", /* 图片访问路径前缀 */
    "imagePathFormat": "/ueditor/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                  /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
                  /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
                  /* {time} 会替换成时间戳 */
                  /* {yyyy} 会替换成四位年份 */
                  /* {yy} 会替换成两位年份 */
                  /* {mm} 会替换成两位月份 */
                  /* {dd} 会替换成两位日期 */
                  /* {hh} 会替换成两位小时 */
                  /* {ii} 会替换成两位分钟 */
                  /* {ss} 会替换成两位秒 */
};