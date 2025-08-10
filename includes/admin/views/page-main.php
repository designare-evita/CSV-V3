<?php
/**
 * View-Datei für das Haupt-Import-Dashboard.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1>CSV Import für Landingpages</h1>

	<?php if ( isset( $_GET['result'] ) && isset( $_GET['message'] ) ) : ?>
		<div class="notice notice-<?php echo $_GET['result'] === 'success' ? 'success' : 'error'; ?> is-dismissible">
			<p><?php echo esc_html( urldecode( $_GET['message'] ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php 
    $progress = csv_import_get_progress();
    if ( $progress['status'] === 'processing' ) : 
    ?>
		<div class="notice notice-info">
			<p><strong>Import läuft:</strong> <?php echo esc_html( $progress['processed'] ); ?> von <?php echo esc_html( $progress['total'] ); ?> Zeilen verarbeitet (<?php echo esc_html( $progress['percent'] ); ?>%)</p>
			<div class="progress-bar-container">
				<div class="progress-bar-fill" style="width: <?php echo esc_attr( $progress['percent'] ); ?>%;"></div>
			</div>
		</div>
	<?php endif; ?>

	<?php 
    $config_valid = csv_import_validate_config( csv_import_get_config() );
    if ( ! $config_valid['valid'] ) : 
    ?>
		<div class="notice notice-error">
			<p><strong>Konfigurationsfehler:</strong></p>
			<ul style="margin-left: 20px; list-style-type: disc;">
				<?php foreach ( $config_valid['errors'] as $error ) : ?>
					<li><?php echo esc_html( $error ); ?></li>
				<?php endforeach; ?>
			</ul>
			<p>
				<a href="<?php echo esc_url( admin_url( 'tools.php?page=csv-import-settings' ) ); ?>"
				   class="button button-primary">⚙️ Einstellungen konfigurieren</a>
			</p>
		</div>
	<?php endif; ?>

	<div class="csv-import-dashboard">
		<div class="csv-import-main-grid">
			<div class="card">
				<h2>🔗 Dropbox Import</h2>
				<p>Importiert die CSV-Datei von der in den Einstellungen hinterlegten Dropbox-URL.</p>
				<?php if ( $config_valid['dropbox_ready'] && $progress['status'] !== 'processing' ) : ?>
					<p>
						<button data-source="dropbox" class="button button-primary button-large csv-import-btn"
						   onclick="return confirm('Dropbox Import wirklich starten?');">
							🚀 Dropbox Import starten
						</button>
					</p>
				<?php else : ?>
					<p>
						<button class="button button-primary button-large" disabled>
							🚀 Dropbox Import starten
						</button><br>
						<small class="error-text">
							<?php if ( $progress['status'] === 'processing' ) : ?>
								⏳ Import läuft bereits
							<?php else : ?>
								⚠️ Konfiguration unvollständig oder Dropbox-URL fehlt/ist ungültig.
							<?php endif; ?>
						</small>
					</p>
				<?php endif; ?>
			</div>

			<div class="card">
				<h2>📁 Lokaler Import</h2>
				<p>Importiert die CSV-Datei vom in den Einstellungen hinterlegten lokalen Serverpfad.</p>
				<?php if ( $config_valid['local_ready'] && $progress['status'] !== 'processing' ) : ?>
					<p>
						<button data-source="local" class="button button-primary button-large csv-import-btn"
						   onclick="return confirm('Lokalen Import wirklich starten?');">
							🚀 Lokalen Import starten
						</button>
					</p>
				<?php else : ?>
					<p>
						<button class="button button-primary button-large" disabled>
							🚀 Lokalen Import starten
						</button><br>
						<small class="error-text">
							<?php if ( $progress['status'] === 'processing' ) : ?>
								⏳ Import läuft bereits
							<?php else : ?>
								⚠️ Konfiguration unvollständig oder lokale CSV-Datei nicht gefunden/lesbar.
							<?php endif; ?>
						</small>
					</p>
				<?php endif; ?>
			</div>

			<div class="card">
    <h2>📊 System Status</h2>
    <?php
    $health        = csv_import_system_health_check();
    $health_labels = [
        'memory_ok'         => 'Memory Limit',
        'php_version_ok'    => 'PHP Version',
        'disk_space_ok'     => 'Freier Speicher',
        'permissions_ok'    => 'Schreibrechte',
        'time_ok'           => 'Ausführungszeit',
        'curl_ok'           => 'cURL Extension',
        'wp_version_ok'     => 'WordPress Version',
        'import_locks_ok'   => 'Import Locks',        // NEU
        'no_stuck_processes' => 'Hängende Prozesse'   // NEU
    ];
    ?>
    <ul class="status-list">
        <?php foreach ( $health as $check => $status ) : ?>
            <?php if(isset($health_labels[$check])): ?>
            <li>
                <?php echo $status ? '✅' : '❌'; ?>
                <?php echo esc_html( $health_labels[ $check ] ); ?>
            </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>

			<div class="card">
				<h2>📈 Statistiken</h2>
				<?php $stats = csv_import_get_stats(); ?>
				<ul class="status-list">
					<li><strong>Gesamt importiert:</strong> <?php echo esc_html( get_option( 'csv_import_total_imported', 0 ) ); ?></li>
					<li><strong>Letzter Import:</strong>
						<?php
						$last_run = get_option( 'csv_import_last_run' );
						echo esc_html( $last_run ? mysql2date( 'd.m.Y H:i', $last_run ) : 'Nie' );
						?>
					</li>
					<li><strong>Letzte Anzahl:</strong> <?php echo esc_html( get_option( 'csv_import_last_count', 0 ) ); ?></li>
					<li><strong>Letzte Quelle:</strong> <?php echo esc_html( get_option( 'csv_import_last_source', '-' ) ); ?></li>
				</ul>
			</div>
		</div>

<div class="bottom-actions">
    <a href="<?php echo esc_url( admin_url( 'tools.php?page=csv-import-settings' ) ); ?>"
       class="button button-secondary">⚙️ Alle Einstellungen</a>
    <button type="button" class="button button-secondary" onclick="location.reload();">🔄 Seite aktualisieren</button>

    <?php // [!code ++] ?>
    <?php // NEUER RESET-BUTTON HINZUGEFÜGT ?>
    <a href="<?php echo esc_url( wp_nonce_url( admin_url('tools.php?page=csv-import&csv_emergency_reset=1'), 'csv_import_emergency_reset' ) ); ?>"
       class="button button-danger" style="color: #d63638; border-color: #d63638;"
       onclick="return confirm('Notfall-Reset wirklich durchführen? Alle Sperren werden entfernt.');">
        🚨 Notfall-Reset
    </a>
    <?php // [!code ++] ?>
</div>
