// alert-error
// alert-success
// alert-info
// alert-warning
const icons = {
  error: "error",
  success: "check_circle",
  info: "info",
  warning: "warning",
};
const body = document.querySelector("body");

const alertarea = document.createElement("div");
alertarea.classList.add("toast");
alertarea.classList.add("toast-center");

body.appendChild(alertarea);

function create_alert(message, duration = 3, type = "error") {
  const alt = document.createElement("div");
  alt.classList.add(`alert`);
  if (["error", "success", "info", "warning"].indexOf(type) !== -1) {
    alt.classList.add(`alert-${type}`);
  } else {
    type = "info";
  }
  const icon = document.createElement("span");
  icon.classList.add("material-symbols-rounded");
  icon.innerText = icons[type];
  const text = document.createElement("span");
  text.innerText = message;
  alt.appendChild(text);
  alertarea.appendChild(alt);
  var anim = setTimeout(
    () => alt.animate([{ opacity: 1 }, { opacity: 0 }], 500),
    duration * 1000 + 500
  );
  var rem = setTimeout(
    () => alertarea.removeChild(alt),
    duration * 1000 + 1000
  );
  alt.addEventListener("click", () => {
    clearTimeout(anim);
    clearTimeout(rem);
    alt.animate([{ opacity: 1 }, { opacity: 0 }], 500);
    setTimeout(() => alertarea.removeChild(alt), 475);
  });
}

window.create_alert = create_alert;
