<?php
/** Partial: tampilkan pesan flash (sukses / error). */
$__ok  = flash_get('success');
$__bad = flash_get('error');
?>
<?php if ($__ok): ?>
    <div class="alert alert-success" role="status"><?= e($__ok) ?></div>
<?php endif; ?>
<?php if ($__bad): ?>
    <div class="alert alert-error" role="alert"><?= e($__bad) ?></div>
<?php endif; ?>
