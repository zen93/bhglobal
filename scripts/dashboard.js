function convert_currency() {
  var amt = base = foreign = "";
  amt = document.getElementById("amount").value;
  base = document.getElementById("base").value;
  foreign = document.getElementById("foreign").value;
  result = document.getElementById("convertedAmt");

  if(base == foreign) {
    result.innerHTML = parseFloat(amt).toFixed(2);
    return;
  }

  temp = document.getElementById(base).value.split('@');
  baseScale = temp[0];

  temp = document.getElementById(foreign).value.split('@');
  foreignScale = temp[0];


  foreignScale = document.getElementById(foreign).value;
  convertedAmt = (parseFloat(foreignScale) / parseFloat(baseScale)) * parseFloat(amt);
  result.innerHTML = convertedAmt.toFixed(2);
}
