<div class="header">
 <?php Prefs_UI::generateNavigationCell('syncml') ?>
 <?php echo htmlspecialchars($prefGroups['syncml']['label']) ?>
 <br class="clear" />
</div>

<div class="prefsContainer item">

<div class="smallheader">
 <?php echo _("Timestamps of successful synchronization sessions") ?>
</div>
<?php if (!$devices): ?>
<em><?php echo _("None") ?></em>
<?php else: ?>
<table class="striped">
<tr>
 <th><?php echo _("Device") ?></th>
 <th><?php echo _("Database") ?></th>
 <th><?php echo _("Server Time") ?></th>
 <th><?php echo _("Client Anchor") ?></th>
 <th><?php echo _("Delete") ?></th>
</tr>
<?php
foreach ($devices as $device => $anchors) {
    foreach ($anchors as $anchor) {
?>
<tr>
 <td><?php echo htmlspecialchars($device) ?></td>
 <td><?php echo htmlspecialchars($anchor['syncml_db']) ?></td>
 <td><?php echo strftime($prefs->getValue('date_format') . ' %H:%M', $anchor['syncml_serveranchor']) ?></td>
 <td><?php echo htmlspecialchars($anchor['syncml_clientanchor']) ?></td>
 <td>
  <form method="post" action="<?php echo Horde::selfUrl() ?>">
   <?php Util::pformInput() ?>
   <input type="hidden" name="horde_prefs_token" value="<?php echo Horde::getRequestToken('horde_prefs') ?>" />
   <input type="hidden" name="group" value="syncml" />
   <input type="hidden" name="app" value="horde" />
   <input type="hidden" name="deviceid" value="<?php echo htmlspecialchars($device) ?>"/>
   <input type="hidden" name="actionID" value="deleteanchor" />
   <input type="hidden" name="db" value="<?php echo htmlspecialchars($anchor['syncml_db']) ?>" />
   <input type="submit" class="button" value="<?php echo _("Delete") ?>" />
  </form>
 </td>
</tr>
<?php } } ?>
</table>
<?php endif; ?>
<form method="post" action="<?php echo Horde::selfUrl() ?>">
<p>
 <?php Util::pformInput() ?>
 <input type="hidden" name="horde_prefs_token" value="<?php echo Horde::getRequestToken('horde_prefs') ?>" />
 <input type="hidden" name="group" value="syncml" />
 <input type="hidden" name="app" value="horde" />
 <input type="hidden" name="actionID" value="deleteall" />
 <input type="submit" class="button" value="<?php echo _("Delete All SyncML Data") ?>" />
 <input id="prefs_return" type="button" class="button" onclick="cancelSubmit=true;document.location.href='<?php echo Horde::applicationUrl(Util::addParameter('services/prefs.php', 'app', 'horde'), true) ?>'" value="<?php echo _("Return to Options") ?>" />
</p>
</form>

</div>
