<div class="log">
	<div id="entry-filter">
		<span id="current">
			<a id="all" href="#current" title="Show All Entries">All</a> |
			<a id="info" href="#info" title="Show Only Info Entries">Info</a> |
			<a id="debug" href="#debug" title="Show Only Debug Entries">Debug</a> |
			<a id="error" href="#error" title="Show Only Error Entries">Error</a>
		</span>
		Show Entries:
	</div>
<?php foreach( $log as $entry ): ?>
	<div class="entry <?php print strtolower($entry['header']); ?>">
		<div class="header">
			<span class="time"><?php print $entry['time']; ?></span>
			<span class="status-icon <?php print strtolower($entry['header']); ?>"></span>
			<h3><?php print $entry['header']; ?></h3>
		</div>
		<div class="details">
			<?php print $entry['details']; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>