/**
 * Bluz media manager
 *
 * @author   Anton Shevchuk
 * @created  02.09.2016 15:08
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
  media.load = function () {
    // load list of all user's images
    $.ajax('/media/list', {
      dataType: 'json',
      success: function (data) {
        data.forEach(function (image) {
          let file = {
            id: image.id,
            name: image.title,
            size: image.size,
            file: image.file,
            thumb: image.thumb
          };
          media.dropzone.emit('addedfile', file);
          media.dropzone.emit('thumbnail', file, image.thumb);
          media.dropzone.emit('complete', file);
        });
      }
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

  // small userfriendly tips
  media.dropzone.on('dragover', function () {
    media.$upload.css('opacity', 0.5).delay(1000).animate({'opacity': 1}, 2000);
  });
  media.dropzone.on('dragleave dragend drop', function () {
    media.$upload.css('background-color', '#ffffff');
  });

  // use element similar to bluz.ajax
  media.dropzone.on('processing', function () {
    media.$upload.css('background-color', '#ffffff');
    bluz.showLoading();
    media.$progress.removeClass('hide');
  });
  media.dropzone.on('complete', function () {
    bluz.hideLoading();
    media.$progress.addClass('hide');
  });
  media.dropzone.on('totaluploadprogress', function (progress) {
    media.$progress.find('.progress-bar').width(parseInt(progress, 10) + '%');
  });
  // use bluz.notify for errors
  media.dropzone.on('error', function (file, errors, XMLHttpRequest) {
    if (XMLHttpRequest.getResponseHeader('Bluz-Notify')) {
      let notifications = $.parseJSON(XMLHttpRequest.getResponseHeader('Bluz-Notify'));
      notify.set(notifications);
    }
    $(file.previewElement).remove();
  });

  media.dropzone.on('success', function (file, response) {
    // setup delete button
    $(file.previewElement).find('a[data-ajax-method=delete]').data('id', response.id);
  });

  // add files to list
  media.dropzone.on('addedfile', function (file) {
    let $preview = $(file.previewElement);

    // add id to delete button
    $preview.find('a[data-ajax-method=delete]').data('id', file.id);

    // hookup for preview click
    $preview.find('.panel-body').on('click', function () {
      // fire event
      media.$upload.trigger('push.data.bluz', file);
    });

    // hookup for delete button
    $preview.on('success.ajax.bluz', function () {
      media.dropzone.emit('removedfile', file);
    });
  });

  return media;
});
