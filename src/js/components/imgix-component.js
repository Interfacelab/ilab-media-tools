/**
 * Created by jong on 8/8/15.
 */

var ImgixComponents=(function(){
    var byteToHex=function(byte) {
        var hexChar = ["0", "1", "2", "3", "4", "5", "6", "7","8", "9", "A", "B", "C", "D", "E", "F"];
        return hexChar[(byte >> 4) & 0x0f] + hexChar[byte & 0x0f];
    };

    return {
        utilities: {
          byteToHex:byteToHex
      }
    };
})();