<?php defined('C5_EXECUTE') or die('Access Denied.');
    if (!isset($query) || !is_string($query)) {
        $query = (string)$controller->get('query');
    }
?>
<form action="<?=$view->url($resultTarget)?>" method="get" class="form-inline">
    <?php if ($query === ''): ?>
        <input name="search_paths[]" type="hidden" value="<?=htmlentities($baseSearchPath, ENT_COMPAT, APP_CHARSET) ?>" />
    <?php elseif (isset($_REQUEST['search_paths']) && is_array($_REQUEST['search_paths'])): ?>
        <?php foreach ($_REQUEST['search_paths'] as $search_path): ?>
            <input name="search_paths[]" type="hidden" value="<?=htmlentities($search_path, ENT_COMPAT, APP_CHARSET) ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
    <input class="form-control algolia-search-value" type="search" placeholder="Search entire site..." aria-label="Search" name="query" value="" id="headerSearchValue" />
    <?php if ($buttonText): ?>
        <button class="btn btn-primary" type="submit"><?= $buttonText; ?></button>
    <?php endif; ?>
</form>
<?php if (isset($error) && $error): ?>
    <?= $error; ?>
<?php endif; ?>