<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/base.css{mtime path=CSS|base.css}" />
{if $AUTO_CSS}
<link rel="stylesheet" type="text/css" href="/css/{$AUTO_CSS}{mtime path=CSS|$AUTO_CSS}" />
{/if}
<script type="text/javascript" src="/js/Basic.src.js{mtime path=JS|Basic.src.js}"></script>
</head>
<body>
Smart Phone template<br />
User Agent : {$USER_AGENT}<br />
{if $QUERIES}
QUERIES : <br />
{foreach from=$QUERIES key=i item=val}
{$i} = {$val}<br />
{/foreach}
{/if}
<br />
Log In Page
<br />
<form method="POST">
	<input type="hidden" name="action" value="login" />
	<input type="submit" value="Log In" />
</form>
</body>
</html>