<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

/**
 * The curated import is driven by a JSON manifest rather than by whatever
 * happens to be in a folder, so that the selection — which photograph, under
 * what title, with what alt text — is reviewable in a diff instead of living
 * in a shell command someone ran once.
 *
 * A manifest with a missing slug or a duplicate one silently mis-imports:
 * two entries collapse onto one file, or a photograph lands under a camera
 * filename. These assert the shape the command relies on.
 */
class GalleryManifestTest extends TestCase
{
    private const PATH = 'database/data/gallery-muhindo.json';

    /** @return list<array<string, mixed>> */
    private function manifest(): array
    {
        $path = base_path(self::PATH);
        $this->assertFileExists($path);

        $rows = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($rows, 'the manifest must be a JSON array');
        $this->assertNotEmpty($rows);

        return $rows;
    }

    public function test_every_entry_carries_the_fields_the_importer_requires(): void
    {
        foreach ($this->manifest() as $i => $row) {
            foreach (['file', 'slug', 'title', 'alt', 'category'] as $key) {
                $this->assertArrayHasKey($key, $row, "entry {$i}");
                $this->assertIsString($row[$key]);
                $this->assertNotSame('', trim($row[$key]), "entry {$i} has an empty {$key}");
            }
        }
    }

    public function test_slugs_and_source_files_are_unique(): void
    {
        $rows = $this->manifest();

        foreach (['slug', 'file'] as $key) {
            $values = array_column($rows, $key);
            $this->assertSame(count($values), count(array_unique($values)),
                "two entries share a {$key}, so one would overwrite the other");
        }
    }

    /**
     * The slug becomes the filename and the public URL. A camera name that
     * slipped through would put 460a4341.jpg on the site, which is the thing
     * the manifest exists to prevent.
     */
    public function test_slugs_are_readable_words_rather_than_camera_filenames(): void
    {
        foreach ($this->manifest() as $row) {
            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9]*(-[a-z0-9]+)+$/', $row['slug'],
                "'{$row['slug']}' is not a lowercase hyphenated name");
            $this->assertGreaterThanOrEqual(2, substr_count($row['slug'], '-') + 1,
                "'{$row['slug']}' is a single word; give it a describable name");
        }
    }

    /** Alt text carries the picture for anyone who cannot see it; a title repeated is not alt text. */
    public function test_alt_text_describes_the_photograph_rather_than_repeating_the_title(): void
    {
        foreach ($this->manifest() as $row) {
            $this->assertNotSame(strtolower($row['title']), strtolower($row['alt']), $row['slug']);
            $this->assertGreaterThan(30, strlen($row['alt']),
                "the alt text for '{$row['slug']}' is too short to describe anything");
        }
    }
}
