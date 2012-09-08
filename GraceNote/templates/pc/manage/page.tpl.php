<div style="text-align: center; margin-bottom: 3px;">
<? if($FROM - $ITEM_NUM >= 0): ?>
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($QUERIES['EXTRA'])): ?><?= $QUERIES['EXTRA']; ?>/<? endif; ?>?from=<?= $FROM - $ITEM_NUM; ?><? if($COLUMN): ?>&column=<?= $COLUMN; ?><? endif; ?><? if($SEARCH): ?>&search=<?= $SEARCH; ?><? endif; ?>#record_list"><?= text($CONTENTS, 'back_btn', '< Back'); ?></a>
<? else: ?>
<span style="color: #CCCCCC"><?= text($CONTENTS, 'back_btn', '< Back'); ?></span>
<? endif; ?>
&nbsp;[<?= $PAGE; ?>]&nbsp;
<? if($FROM + $ITEM_NUM < $TOTAL): ?>
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($QUERIES['EXTRA'])): ?><?= $QUERIES['EXTRA']; ?>/<? endif; ?>?from=<?= $FROM + $ITEM_NUM; ?><? if($COLUMN): ?>&column=<?= $COLUMN; ?><? endif; ?><? if($SEARCH): ?>&search=<?= $SEARCH; ?><? endif; ?>#record_list"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></a>
<? else: ?>
<span style="color: #CCCCCC"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></span>
<? endif; ?>
</div>
