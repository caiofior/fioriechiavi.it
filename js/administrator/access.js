
function updateAccess() {
  $("#accessLog").load(
    window.location+"&action=data&no_log",
    null,
    setTimeout(updateAccess, 60000)
  );

}
setTimeout(updateAccess, 1000);
