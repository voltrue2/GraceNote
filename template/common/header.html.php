<?= Loader::jsVars('window'); ?>
<div class="header" style="clear: both;">
<div class="header-logo" style="float: left;">
<a href="/"><img src="/img/logos/GraceNote.png" /></a>
</div>
<? if (isset($cmsUser)): ?>
<div style="float: right; color: #333; font-size: 15px; line-height: 40px;  padding-right: 20px;"><?= $cmsUser['user']; ?></div>
<div class="user-button" style="float: right;" onmouseup="window.user.openMenu();"></div>
<? endif; ?>
</div>
<? if (UserAgent::isBrowser('IE')): ?>
<?= '<!-- We do not like IE -->'; ?>
<div style="position: fixed; top: 0, left: 0; z-index: 999; width: 100%; height: 100%; background: #fff;">
<div style="color: #f00; font-size: 30px; font-weight: bold; text-align: center;" >This application does not support Windows Internet Explorer</div>
<div style="text-align: center; font-size: 20px; color: #666;">Suggested Browsers</div>
<div style="text-align: center; "><a href="http://www.google.com/intl/en/chrome/browser/">Google Chrome</a></div>
<div style="text-align: center; "><a href="http://www.mozilla.org/en-US/firefox/">Mozilla Firefox</a></div>
</div>
<? endif; ?>
