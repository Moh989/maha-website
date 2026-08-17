import Foundation
import AppKit

guard CommandLine.arguments.count == 2,
      let image = NSImage(contentsOfFile: CommandLine.arguments[1]),
      let tiff = image.tiffRepresentation,
      let bitmap = NSBitmapImageRep(data: tiff) else {
    exit(64)
}

var counts: [String: Int] = [:]
var opaque = 0
for y in 0..<bitmap.pixelsHigh {
    for x in 0..<bitmap.pixelsWide {
        guard let color = bitmap.colorAt(x: x, y: y)?.usingColorSpace(.sRGB) else { continue }
        if color.alphaComponent < 0.5 { continue }
        opaque += 1
        let red = Int((color.redComponent * 255).rounded())
        let green = Int((color.greenComponent * 255).rounded())
        let blue = Int((color.blueComponent * 255).rounded())
        let key = String(format: "#%02X%02X%02X", red, green, blue)
        counts[key, default: 0] += 1
    }
}

print("size=\(bitmap.pixelsWide)x\(bitmap.pixelsHigh) opaque=\(opaque)")
for item in counts.sorted(by: { $0.value > $1.value }).prefix(16) {
    let percentage = Double(item.value) / Double(max(opaque, 1)) * 100
    print(String(format: "%@ %d %.2f%%", item.key, item.value, percentage))
}
