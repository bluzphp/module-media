/**
 * Bluz media manager
 *
 * @author   Anton Shevchuk
 * @created  02.09.2016 15:08
 */
/* global define,require*/
define(['jquery', 'bluz', 'bluz.notify', 'dropzone'], function ($, bluz, notify, Dropzone) {
  'use strict';

  // Upload container
  let uploadNode = document.getElementById('upload');

  // Get the template HTML and remove it from the document
  let previewNode = document.getElementById('template');
  previewNode.id = '';

  let previewTemplate = previewNode.parentNode.innerHTML;
  previewNode.parentNode.removeChild(previewNode);

  let progressNode = document.getElementById('progress');

  let myDropzone = new Dropzone(uploadNode, {
    url: '/media/upload', // Set the url
    thumbnailWidth: 160,
    thumbnailHeight: 160,
    parallelUploads: 8,
    previewTemplate: previewTemplate,
    autoQueue: true, // Make sure the files aren't queued until manually added
    previewsContainer: '#previews', // Define the container to display the previews
    clickable: '.fileinput-button' // Define the element that should be used as click trigger to select files.
  });

  // small userfriendly tips
  myDropzone.on('dragover', function () {
    $(uploadNode).css('background-color', '#ddffdd');
  });
  myDropzone.on('dragleave dragend drop', function () {
    $(uploadNode).css('background-color', '#ffffff');
  });

  // use element similar to bluz.ajax
  myDropzone.on('processing', function () {
    $(uploadNode).css('background-color', '#ffffff');
    bluz.showLoading();
    $(progressNode).removeClass('hide');
  });
  myDropzone.on('complete', function () {
    bluz.hideLoading();
    $(progressNode).addClass('hide');
  });
  myDropzone.on('totaluploadprogress', function (progress) {
    $(progressNode).find('.progress-bar').width(parseInt(progress, 10) + '%');
  });
  // use bluz.notify for errors
  myDropzone.on('error', function (file, errors, XMLHttpRequest) {
    if (XMLHttpRequest.getResponseHeader('Bluz-Notify')) {
      let notifications = $.parseJSON(XMLHttpRequest.getResponseHeader('Bluz-Notify'));
      notify.set(notifications);
    }
    $(file.previewElement).remove();
  });

  myDropzone.on('success', function (file, response) {
    // setup delete button
    $(file.previewElement).find('a[data-ajax-method=delete]').data('id', response.id);
  });

  // add files to list
  myDropzone.on('addedfile', function (file) {
    let $preview = $(file.previewElement);

    // add id to delete button
    $preview.find('a[data-ajax-method=delete]').data('id', file.id);

    // hookup for preview click
    $preview.find('.panel-body').on('click', function () {
      // fire event
      $(uploadNode).trigger('push.data.bluz', file);
    });

    // hookup for delete button
    $preview.on('success.ajax.bluz', function () {
      myDropzone.emit('removedfile', file);
    });
  });

  // load list of all user's images
  $.ajax('/media/list', {
    dataType: 'json',
    success: function (data) {
      data.forEach(function (element) {
        let image = element;
        let file = {
          id: image.id,
          name: image.title,
          size: image.size,
          file: image.file,
          thumb: image.thumb
        };

        myDropzone.emit('addedfile', file);
        myDropzone.emit('thumbnail', file, image.thumb);
        myDropzone.emit('complete', file);
      });
    }
  });

  return myDropzone;
});
