const insertAfter = (newNode, referenceNode) => {
  referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
};

document.addEventListener("DOMContentLoaded", (event) => {
  const container = document.querySelector("#ctc_toc_container");
  if ((h2s = document.querySelectorAll(".primary article h2"))) {
    let listitems = "";
    h2s.forEach((h2) => {
      let title = h2.innerText;
      // get rid of punctuation, etc for anchor link id
      let id = encodeURIComponent(
        title
          .replaceAll(" ", "-")
          .replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g, "")
          .replace(/\s{2,}/g, " ")
      );

      // add ids for anchor links
      h2.setAttribute("id", id);

      let backto_a = document.createElement("a");
      backto_a.setAttribute("href", "#banner");
      backto_a.innerHTML = "Back to Top";
      backto_a.addEventListener("DOMContentLoaded", (e) => {
        e.preventDefault();
        window.scrollTo(0, 0);
      });

      let backto_span = document.createElement("div");
      backto_span.setAttribute("class", "to-top");
      backto_span.appendChild(backto_a);

      insertAfter(backto_span, h2);

      // build the table of contents list
      listitems += '<li><a href="#' + id + '">' + title + "</a></li>";
    });

    let toc = "<ul>" + listitems + "</ul>";
    container.innerHTML = toc;
  }
});
