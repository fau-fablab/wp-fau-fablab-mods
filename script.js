function setDoorState(state, text) {
  jQuery(".fablab_doorstate_widget, .fablab_doorstate_text").each(function(){
    var element = jQuery(this);
    if (element.text() != text || !element.hasClass(state)) {
      element.fadeOut("slow", function() {
	element.removeClass("opened closed outdated").addClass(state).text(text).fadeIn();
      });
    }
  });
  jQuery(".fablab_doorstate_badge").removeClass("opened closed outdated").addClass(state).fadeIn();
}
function updateDoorState() {
  jQuery.getJSON("/spaceapi/door/", function(data) {
    var outdated = (new Date() / 1000 - data.time) > (60 * 60 * 24 * 7);
    // new Date() / 1000: get current timestamp in sec instead of msec
    // the info is outdated if it is older than one week
    setDoorState(
      outdated ? "outdated" : data.state,
      data.text + (outdated ? " (Diese Information ist evtl. veraltet.) " : "")
    );
  });
}
function addSiteDescriptionDoorIndicator() {
  jQuery('.site-description').append(`
    <br>
    <span>
      <svg class="fablab_doorstate_badge">
        <defs>
          <filter id="status-filter-gauss" height="2.2" width="2.2" y="-50%" x="-50%">
            <feGaussianBlur stdDeviation="3" />
          </filter>
        </defs>
        <circle r="25%" cy="50%" cx="50%"/>
        <circle r="18%" cx="50%" cy="50%" style="filter:url(#status-filter-gauss)"/>
      </svg>
      <span class="fablab_doorstate_text"></span>
    </span>
  `);
}
jQuery(document).ready(function() {
  addSiteDescriptionDoorIndicator();
  updateDoorState();
  window.setInterval(updateDoorState, 60 * 1000);
});
