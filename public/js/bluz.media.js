/**
 * Bluz media manager
 *
 * @author   Anton Shevchuk
 * @created  02.09.2016 15:08
 *
 * Events:
 *  - push.data.bluz
 */
/* global define,require*/
define(['jquery', 'bluz', 'bluz.notify', 'dropzone'], function ($, bluz, notify, Dropzone) {
  'use strict';

  // Media object
  let media = {
    $upload: $('#media-upload'),
    $previews: $('#media-previews'),
    $progress: $('#media-progress'),
    template: $('#media-template').html()
  };

  // Load list of images by AJAX
  media.load = () => {
    media.$upload = $('#media-upload');
    media.$previews = $('#media-previews');
    media.$progress = $('#media-progress');
    media.template = $('#media-template').html();

    // hookup for preview click
    media.$previews.on('click', '.card img', function () {
      let image = $(this).parents('.card').data();
      // fire event
      media.$upload.trigger('push.bluz.data', image);
      return false;
    });

    // hookup for delete click
    media.$previews.on('success.bluz.ajax', 'a[data-ajax-method=delete]', function () {
      $(this).parents('div.image-preview').remove();
    });
  };

  // Init Dropzone plugin
  media.dropzone = new Dropzone(media.$upload.get(0), {
    url: '/media/upload', // Set the url
    thumbnailWidth: 160,
    thumbnailHeight: 160,
    parallelUploads: 8,
    previewTemplate: media.template,
    autoQueue: true, // Make sure the files aren't queued until manually added
    previewsContainer: media.$previews.get(0), // Define the container to display the previews
    clickable: '.fileinput-button' // Define the element that should be used as click trigger to select files.
  });

  media.dropzone
    .on('dragover', () => {
      // small userfriendly tips
      media.$upload.css('opacity', 0.5).animate({'opacity': 1}, 3000);
    })
    .on('processing', () => {
      // use element similar to bluz.ajax
      bluz.showLoading();
      media.$progress.removeAttr('hidden');
    })
    .on('complete', () => {
      bluz.hideLoading();
      media.$progress.attr('hidden', '1');
      media.$upload.stop(true, true);
    })
    .on('totaluploadprogress', function (progress) {
      media.$progress.find('.progress-bar').width(parseInt(progress, 10) + '%');
    })
    .on('error', function (file, errors, XMLHttpRequest) {
      if (XMLHttpRequest.getResponseHeader('Bluz-Notify')) {
        let notifications = $.parseJSON(XMLHttpRequest.getResponseHeader('Bluz-Notify'));
        notify.set(notifications);
      }
      $(file.previewElement).remove();
    })
    .on('success', function (image, response) {
      let $preview = $(image.previewElement);
      // setup delete button
      $preview.find('a[data-ajax-method=delete]').data('id', response.id);
      // listen event
      $preview.on('success.bluz.ajax', () => {
        media.dropzone.emit('removedfile', image);
      });
    });

  return media;
});
