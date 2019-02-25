<?php defined('C5_EXECUTE') or die('Access Denied.');
$currentPage = Page::getCurrentPage();
if (!isset($query) || !is_string($query)) {
    $query = '';
}
?>
<?php if (!$currentPage->isEditMode()): ?>
    <?php //// TODO: Add the Algalia search via NPM ?>
    <script src="https://cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
<?php endif; ?>

<div id="algoliaResults-<?= $bID ?>">
    <div class="input-group col-sm-12 col-md-4">
        <input type="search" name="query" placeholder="Search entire site..." value="" class="form-control algolia-search-value" />
    </div>
    <div class="algolia-no-results" <?php if ($query): ?>style="display: none;"<?php endif; ?>>
        <h4 style="margin-top:32px"><?=t('There were no results found. Please try another keyword or phrase.')?></h4>
    </div>
    <div class="algolia-searching" <?php if (!$query): ?>style="display: none;"<?php endif; ?>>
        <h4 style="margin-top:32px"><span class="fa fa-refresh fa-spin"></span> <?=t('Searching for results...')?></h4>
    </div>
    <div class="algolia-search-results">
    </div>
</div>

<script type="text/template" role="algolia-result" id="algoliaResultTemplate-<?= $bID ?>">
    <div class="searchResult">
        <h3><a href="<%= path %>"><%= name %></a></h3>
        <p>
            <%= content %>
            <br/>
            <a href="<%= path %>" class="pageLink">
                <%= path %>
            </a>
        </p>
    </div>
</script>
<?php if (Config::get('algolia_search::algolia.application_id') && !$currentPage->isEditMode()): ?>
    <script type="text/javascript">
        try {
            var algoliaClient = algoliasearch('<?= Config::get('algolia_search::algolia.application_id'); ?>', '<?= Config::get('algolia_search::algolia.search_api_key'); ?>');
            var algoliaIndex = algoliaClient.initIndex('<?= Config::get('algolia_search::algolia.index_key'); ?>');
            // algoliaIndex.setSettings({
            //     attributesToHighlight: [
            //         'description',
            //         'content'
            //     ]
            // });
            var algoliaResultsContainer, algoliaResultTemplate;
            $(function() {
                algoliaResultsContainer = $('#algoliaResults-<?= $bID ?> .algolia-search-results');
                algoliaResultTemplate = _.template($('#algoliaResultTemplate-<?= $bID ?>').html());
                var urlParams = _.object(_.compact(_.map(window.location.search.slice(1).split('&'), function(item) {  if (item) return item.split('='); })));
                triggerAlgoliaSearch(urlParams.query);
                $('.algolia-search-value').on('keyup', _.debounce(function(e) {
                    e.preventDefault();
                    triggerAlgoliaSearch($(this).val());
                }, 100));
            });
        } catch (e) {
            console.log(e);
        }

        function triggerAlgoliaSearch(query) {
            $('.algolia-search-value').val(query);
            algoliaIndex.search({
                    query: query
                },
                function searchDone(err, content) {
                    $(algoliaResultsContainer).html('');
                    $('#algoliaResults-<?= $bID ?> .algolia-searching').hide();
                    $('#algoliaResults-<?= $bID ?> .algolia-no-results').hide();
                    if (content.hits && content.hits.length > 0) {
                        $(content.hits).each(function(index, hit){
                            if (hit._highlightResult) {
                                if (hit._highlightResult.name && hit._highlightResult.name.value) {
                                    hit.name = hit._highlightResult.name.value;
                                }
                                if (hit._highlightResult.content && hit._highlightResult.content.value) {
                                    hit.content = hit._highlightResult.content.value;
                                }
                                if (hit._highlightResult.description && hit._highlightResult.description.value) {
                                    hit.description = hit._highlightResult.description.value;
                                }
                                if (hit._highlightResult.path && hit._highlightResult.path.value) {
                                    hit.path = hit._highlightResult.path.value;
                                }
                            }
                            if (!hit.content || hit.content == '') {
                                hit.content = (hit.description) ? hit.description : '';
                            }
                            var length = Math.min(hit.content.length, 300);
                            var suffix = (hit.content.length > 100) ? '...' : '   ';
                            hit.content = hit.content.substring(0, length);
                            $(algoliaResultsContainer).append(algoliaResultTemplate(hit));
                        });
                    } else {
                        $('#algoliaResults-<?= $bID ?> .algolia-no-results').show();
                    }
                    if (err) throw err;
                }
            );
        }
    </script>
<?php endif; ?>