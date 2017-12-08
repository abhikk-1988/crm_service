toastr.options = {
  closeButton: true,
  debug: false,
  newestOnTop: true,
  progressBar: false,
  positionClass: "toast-bottom-right",
  preventDuplicates: false,
  onclick: null,
  showDuration: 500,
  hideDuration: 300,
  timeOut: 5000,
  extendedTimeOut: 1000,
  showEasing: "swing",
  hideEasing: "linear",
  showMethod: "slideDown",
  hideMethod: "slideUp",
  preventDuplicates: true,
  timeOut: 30000,
  extendedTimeOut: 20000,
}

function showToast(msg,title,type)
{
    if(type == 'error'){
      toastr.options.positionClass = 'toast-bottom-full-width';
    }else if(type == 'success'){
      toastr.options.positionClass = 'toast-bottom-right';
    }
    toastr[type](msg, title);
}

