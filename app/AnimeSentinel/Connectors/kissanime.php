<?php

namespace App\AnimeSentinel\Connectors;

use App\Video;
use App\AnimeSentinel\Helpers;
use App\AnimeSentinel\Downloaders;
use Carbon\Carbon;

class kissanime
{
  /**
   * Finds all video's for the requested show.
   * Returns data as an array of models.
   *
   * @return array
   */
  public static function seek($show) {
    $videos = [];

    // Try all alts to get a valid episode page
    foreach ($show->alts as $alt) {
      $page = Downloaders::downloadCloudFlare('http://kissanime.to/Anime/'.str_urlify($alt), 'kissanime');
      if (strpos($page, '<meta name="description" content="Watch online and download ') !== false) {
        $videos = array_merge($videos, Self::seekEpisodes($page, $show, $alt, [
          'link_stream' => 'http://kissanime.to/Anime/'.str_urlify($alt),
          'translation_type' => 'sub',
        ]));
      }

      $page = Downloaders::downloadCloudFlare('http://kissanime.to/Anime/'.str_urlify($alt).'-dub', 'kissanime');
      if (strpos($page, '<meta name="description" content="Watch online and download ') !== false) {
        $videos = array_merge($videos, Self::seekEpisodes($page, $show, $alt, [
          'link_stream' => 'http://kissanime.to/Anime/'.str_urlify($alt).'-dub',
          'translation_type' => 'dub',
        ]));
      }
    }

    return $videos;
    // Stream specific:   'show_id', 'streamer_id', 'translation_type', 'link_stream'
    // Episode specific:  'episode_num', 'link_episode', ('notes')
    // Video specific:    'uploadtime', 'link_video', 'resolution'
  }

  private static function seekEpisodes($page, $show, $alt, $data) {
    $videos = [];

    // Set some general data
    $data_stream = array_merge($data, [
      'show_id' => $show->id,
      'streamer_id' => 'kissanime',
    ]);

    // Scrape the page for episode data
    $episodes = Helpers::scrape_page(str_get_between($page, '<tr style="height: 10px">', '</table>'), '</td>', [
      'episode_num' => [true, 'Watch anime ', ' in high quality'],
      'link_episode' => [false, 'href="', '"'],
      'uploadtime' => [false, '<td>', ''],
    ]);

    // Get mirror data for each episode
    foreach ($episodes as $episode) {
      // Complete episode data
      $episode = Self::seekCompleteEpisode($episode);
      if (empty($episode)) {
        continue;
      }

      // Get all mirrors data
      $mirrors = Self::seekMirrors($episode['link_episode']);

      // Loop through mirror list
      foreach ($mirrors as $mirror) {
        // Complete mirror data
        $mirror = Self::seekCompleteMirror($mirror);
        // Create and add final video
        $videos[] = new Video(array_merge($data_stream, $episode, $mirror));
      }
    }

    return $videos;
  }

  private static function seekMirrors($link_episode) {
    // Get episode page
    $page = Downloaders::downloadCloudFlare($link_episode, 'kissanime');
    // Scrape the page for mirror data
    $mirrors = Helpers::scrape_page(str_get_between($page, 'id="divDownload">', '</div>'), '</a>', [
      'link_video' => [true, 'href="', '"'],
      'resolution' => [false, '>', '.'],
    ]);
    return $mirrors;
  }

  private static function seekCompleteEpisode($episode) {
    // Complete episode data
    if (strpos($episode['link_episode'], '/Episode-') !== false) {
      $line = $episode['episode_num'];
      $episode['episode_num'] = str_get_between($line, 'Episode ', ' ');
      $episode['notes'] = str_get_between($line, 'Episode '.$episode['episode_num'].' ', ' online');
    }
    elseif (strpos($episode['link_episode'], '/Movie?') !== false) {
      $episode['notes'] = str_get_between($episode['episode_num'], 'Movie ', ' online', true);
      $episode['episode_num'] = 1;
    }
    else {
      return false;
    }
    $episode['notes'] = str_replace('[', '(', str_replace(']', ')', $episode['notes']));
    $episode['link_episode'] = 'http://kissanime.to'.$episode['link_episode'];
    $episode['uploadtime'] = Carbon::createFromFormat('n/j/Y', trim($episode['uploadtime']))->hour(12)->minute(0)->second(0);
    return $episode;
  }

  private static function seekCompleteMirror($mirror) {
    // Complete mirror data
    return $mirror;
  }

  /**
   * Finds all video's from the recently aired page.
   * Returns data as an array of models.
   *
   * @return array
   */
  public static function guard() {
    $videos = [];
    //TODO
    return $videos;
  }

  /**
   * Finds the stream link for the requested video.
   *
   * @return string
   */
  public static function videoLink($video) {
    // Get all mirrors data
    $mirrors = Self::seekMirrors($video->link_episode);

    // Loop through mirror list
    foreach ($mirrors as $mirror) {
      // Complete mirror data
      $mirror = Self::seekCompleteMirror($mirror);
      // Determine which link to return
      if ($mirror['resolution'] === $video->resolution) {
        return $mirror['link_video'];
      }
    }

    return $video->link_video;
  }
}
