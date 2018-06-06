var init = (function() {
"use strict";
  
  var _$form = $('form#form'),
      _$body = $('body');
  
  return {
    _$form: _$form,
    _$body: _$body
  };
})();

var logHandler = (function() {
"use strict";
  
  var _$form = init._$form;
  var _$body = init._$body;
  
  function _getContent(status, messageId, param, message) {
    var result = {},
    messages = {};
    messages.error = {
      // Response
      401 : 'Please fill out all mandatory fields.',
    };
    
    messages.success = {
      // Response
      401 : '',
      402 : '',
      403 : '',
      404 : '',
      405 : '',
      406 : '',
    };
    
    result.status = status;
    
    result.message = (messageId === null) ? message : messages[status][messageId];
    
    return result;
  }
  
  function compileMessage(response) {
    
    if(!response)
      return false;
    
    //console.log(response);
    //console.log(_$form);
    
    (response.status === 'error') ? response.status = 'danger' : response.status;
    
    _$body.find('.alert')
      .slideDown()
      .removeClass('alert-success alert-danger alert-info')
      .addClass('alert-' + response.status)
        .children('.msg')
          .text(response.message);
  }
  
  function compileMessageFromArray(response) {
    
    if(!response)
      return false;
    
    response.status = 'info';
    
    _$body.find('.alert')
      .slideDown()
      .removeClass('alert-success alert-danger')
      .addClass('alert-' + response.status)
        .children('.msg')
          .text('');
    
    $.each(response, function( index, message ) {
      
      _$body.find('.alert')
        .children('.msg')
          .append('<p>' + message.message + '</p>');
    
    });
  }
  
  function logError(messageId, param, message) {
    return _getContent('error', messageId, param, message);
  }
  function logSuccess(messageId, param, message) {
    return _getContent('success', messageId, param, message);
  }
  
  return {
    logError: logError,
    logSuccess: logSuccess,
    compileMessage: compileMessage,
    compileMessageFromArray: compileMessageFromArray
  };
})();

var synca = (function() {
"use strict";
  
  var _$form = init._$form;
  var _$body = init._$body;
  
  // Add eventlistener
  _$form.on('submit', function() {
    _insertRow(event);
  });
  
  // Add eventlistener
  _$body.find('#runSync').on('click', function() {
    _sync(event);
  });
  
  // Add eventlistener
  _$body.find('.close').on('click', _close);
  
  // Close status top bar
  function _close() {
    _$body.find('.alert-dismissible').slideUp('slow', function() {
      _$body.find('.msg', this).text('');
    });
  }
  
  // Clear input fields
  function _clearFields() {
    _$form.find('input.form-element').val('');
  }
  
  
  function _setSubmissionData() {
    return {
      'product_name'    : _$form.find('#product_name').val(),
      'price'           : _$form.find('#price').val(),
      'csrf_test_name'  : csrf_token
    };
  }
  
  function _ajaxRequest(method, url, submissionData, callback) {
    $.ajax({
      url: url,
      type: method,
      dataType: 'json',
      data: submissionData,
      
      success: function (data) {
        
        if(data !== null){
          
          if(data.status === 'error'){
            
            logHandler.compileMessage(data);
            
          } else {
            
            callback ? callback(data) : false;
            
            if(!data['status'])
              logHandler.compileMessageFromArray(data);
            else
              logHandler.compileMessage(data);
            
          }
          
          return data;
        }
      },
      
      error: function (data, status, e) {
        alert('ERROR: '+e);
      }
    });
  }
  
  function _sync(event) {
    
    event.preventDefault();
    
    var url = _$body.find('#runSync').attr('href');
    
    _ajaxRequest('GET', url);
  }
  
  function _insertRow(event) {
    
    event.preventDefault();
    
    var url = _$form.attr('action'),
    response,
    submissionData;
    
    // Validation
    response = (_$form.find('#product_name').val() === '' || _$form.find('#price').val() === '') ?
      logHandler.logError(401) : null;
    
    if (response !== null){
      
      logHandler.compileMessage(response);
      
      return false;
    }
    
    submissionData = _setSubmissionData();
    
    _ajaxRequest('POST', url, submissionData, _clearFields);
  }
})();