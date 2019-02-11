function myopen(currentItem) {
  var articleId = currentItem.id;
  var tabObj = document.getElementById("relations");
  var url = "relate.php?a_id=" + articleId;
  console.log(articleId);

  $.get(url, function(data) {
    console.log(data);
    var responses = JSON.parse(data);

    if ($("#relations tr").length > 0) {
      $("#relations tr").remove();
    }
    for (var i = 0; i < 5; i++) {
      var newTr = relations.insertRow();
      var newTd = newTr.insertCell();
      newTd.innerHTML = "<a target=_blank href=" + responses[i][1] + ">" + responses[i][0] + "</a>";
    }
    $("#rel").removeClass("hide-content");
  });
}
