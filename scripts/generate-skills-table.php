<?php
/**
 * Regenerate the contributor skills table in AGENTS.md from skill SKILL.md frontmatter files.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

$repo_root  = dirname( __DIR__ );
$skills_dir = $repo_root . '/.cursor/skills';
$agents_md  = $repo_root . '/AGENTS.md';

if ( ! is_dir( $skills_dir ) ) {
	fwrite( STDERR, "Skills directory not found: {$skills_dir}\n" );
	exit( 1 );
}

if ( ! is_readable( $agents_md ) ) {
	fwrite( STDERR, "AGENTS.md not found: {$agents_md}\n" );
	exit( 1 );
}

$skills = array();

foreach ( glob( $skills_dir . '/*/SKILL.md' ) ?: array() as $skill_file ) {
	$slug     = basename( dirname( $skill_file ) );
	$contents = (string) file_get_contents( $skill_file );
	$meta     = parse_skill_frontmatter( $contents );

	$name        = $meta['name'] ?? $slug;
	$description = $meta['description'] ?? '';

	$description = preg_replace( '/\s+/', ' ', trim( $description ) ) ?? '';

	$skills[ $slug ] = array(
		'name'        => $name,
		'description' => $description,
	);
}

ksort( $skills, SORT_STRING );

$rows = array( '| Skill | Description |', '| --- | --- |' );

foreach ( $skills as $slug => $skill ) {
	$rows[] = sprintf(
		'| [`%s`](.cursor/skills/%s/SKILL.md) | %s |',
		$skill['name'],
		$slug,
		$skill['description']
	);
}

$table = implode( "\n", $rows );

$agents_contents = (string) file_get_contents( $agents_md );
$pattern         = '/<!-- skills-table:start -->.*?<!-- skills-table:end -->/s';

$replacement = "<!-- skills-table:start -->\n{$table}\n<!-- skills-table:end -->";

if ( ! preg_match( $pattern, $agents_contents ) ) {
	fwrite( STDERR, "AGENTS.md is missing skills-table markers.\n" );
	exit( 1 );
}

$updated = (string) preg_replace( $pattern, $replacement, $agents_contents, 1 );

if ( $updated === $agents_contents ) {
	echo "AGENTS.md skills table is already up to date.\n";
	exit( 0 );
}

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- CLI script.
file_put_contents( $agents_md, $updated );
echo "Updated AGENTS.md skills table (" . count( $skills ) . " skills).\n";

/**
 * @param string $contents SKILL.md file contents.
 * @return array<string, string>
 */
function parse_skill_frontmatter( string $contents ): array {
	if ( ! preg_match( '/^---\s*\R(.*?)\R---/s', $contents, $matches ) ) {
		return array();
	}

	$meta       = array();
	$block      = $matches[1];
	$lines      = preg_split( '/\R/', $block ) ?: array();
	$current    = null;
	$collecting = false;

	foreach ( $lines as $line ) {
		if ( preg_match( '/^([a-z0-9_-]+):\s*(.*)$/i', $line, $parts ) ) {
			$current    = $parts[1];
			$value      = trim( $parts[2] );
			$collecting = str_starts_with( $value, '>' ) || str_starts_with( $value, '|' );

			if ( $collecting ) {
				$meta[ $current ] = '';
				continue;
			}

			$meta[ $current ] = trim( $value, " \t\"'" );
			continue;
		}

		if ( $collecting && null !== $current && '' !== trim( $line ) ) {
			$meta[ $current ] .= ( '' === $meta[ $current ] ? '' : ' ' ) . trim( $line );
		}
	}

	return $meta;
}
