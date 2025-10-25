<?php

namespace App\Factory;

use App\Entity\Artist;

class ArtistFactory
{
    /**
     * Create a single Song from Spotify data.
     */
    public static function createFromSpotifyData(array $data): Artist
    {
        $artist = new Artist();

        $artist
            ->setIdSpotify($data['id'] ?? '')
            ->setName($data['name'] ?? '')
            ->setUri($data['uri'] ?? '')
            ->setExternalUrl($data['external_urls']['spotify'] ?? '');

        $artist->setGenres($data['genres'] ?? []);
        $artist->setImageUrl($data['images'][0]['url'] ?? '');
        $artist->setFollowers($data['followers']['total'] ?? 0);
        $artist->setPopularity($data['popularity'] ?? 0);
        return $artist;
    }

    public static function createMultipleFromSpotifyData(array $artistsData): array
    {
        $artist = [];

        foreach ($artistsData as $artistData) {
            $artist[] = self::createFromSpotifyData($artistData);
        }

        return $artist;
    }
}
