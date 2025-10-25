<?php

namespace App\Factory;

use App\Entity\Track;
use App\Service\SpotifyRequestService;

class TrackFactory
{
    /**
     * Create a single Song from Spotify data.
     */
    public static function createFromSpotifyData(array $data, bool $persistArtists = false): Track
    {
        $track = new Track();

        $track
            ->setDiscNumber($data['disc_number'] ?? 0)
            ->setDurationMs($data['duration_ms'] ?? 0)
            ->setExplicit($data['explicit'] ?? false)
            ->setIsrc($data['external_ids']['isrc'] ?? '')
            ->setSpotifyUrl($data['external_urls']['spotify'] ?? '')
            ->setHref($data['href'] ?? '')
            ->setSpotifyId($data['id'] ?? '')
            ->setLocal($data['is_local'] ?? false)
            ->setName($data['name'] ?? '')
            ->setPopularity($data['popularity'] ?? 0)
            ->setPreviewUrl($data['preview_url'] ?? null)
            ->setTrackNumber($data['track_number'] ?? 0)
            ->setType($data['type'] ?? '')
            ->setUri($data['uri'] ?? '')
            ->setPictureLink($data['album']['images'][0]['url'] ?? null);

        if ($persistArtists && isset($data['artists'])) {
            foreach ($data['artists'] as $artistData) {
                $track->addArtist(ArtistFactory::createFromSpotifyData($artistData));
            }
        }

        return $track;
    }


    /**
     * Create an array of Songs from an array of Spotify track data.
     *
     * @param array $tracksData Array of track data from Spotify
     * @return Song[] Array of Song objects
     */
    public static function createMultipleFromSpotifyData(array $tracksData): array
    {
        $songs = [];

        // Iterate over each track data and create a Song object
        foreach ($tracksData as $trackData) {
            $songs[] = self::createFromSpotifyData($trackData, true);
        }

        return $songs;
    }

    public static function enrichArtists(Track $track, SpotifyRequestService $spotifyService, string $token): Track
    {
        $artistIds = array_map(fn($artist) => $artist->getIdSpotify(), $track->getArtists()->toArray());
        if (empty($artistIds)) {
            return $track;
        }
        $fullArtists = $spotifyService->getArtistsByIds($artistIds, $token);
        $track->getArtists()->clear();



        foreach ($fullArtists as $artist) {
            $track->addArtist($artist);
        }

        return $track;
    }
}
