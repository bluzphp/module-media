/**
 * Bluz media manager
 *
 * @author   Anton Shevchuk
 * @created  02.09.2016 15:08
 */
/* global define,require*/
define(['jquery', 'dropzone', 'bluz.notify'], function ($, Dropzone, notify) {
  'use strict';

  // Upload container
  let uploadNode = document.getElementById('upload');

  // Get the template HTML and remove it from the document
  let previewNode = document.getElementById('template');
  previewNode.id = '';

  let previewTemplate = previewNode.parentNode.innerHTML;
  previewNode.parentNode.removeChild(previewNode);

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

  // use element similar to bluz.ajax
  myDropzone.on('processing', function () {
    $('#loading').show();
  });
  myDropzone.on('complete', function () {
    $('#loading').hide();
  });

  // use bluz.notify for errors
  myDropzone.on('error', function (file, errors, XMLHttpRequest) {
    if (XMLHttpRequest.getResponseHeader('Bluz-Notify')) {
      let notifications = $.parseJSON(XMLHttpRequest.getResponseHeader('Bluz-Notify'));
      notify.set(notifications);
    }
  });

  myDropzone.on('addedfile', function (file) {
    let $preview = $(file.previewElement);

    // add id to delete button
    $preview.find('a[data-ajax-method=delete]').attr('data-id', file.id);

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


  $.ajax('/media/list', {
    dataType: 'json',
    success: function (data) {
      for (let i in data) {
        let image = data[i];
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
      }
    }
  });

  return myDropzone;
});
