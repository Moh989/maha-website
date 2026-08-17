import Foundation
import PDFKit
import AppKit

guard CommandLine.arguments.count == 3 else {
    fputs("Usage: inspect_profile.swift input.pdf output-directory\n", stderr)
    exit(64)
}

let inputURL = URL(fileURLWithPath: CommandLine.arguments[1])
let outputURL = URL(fileURLWithPath: CommandLine.arguments[2], isDirectory: true)
try FileManager.default.createDirectory(at: outputURL, withIntermediateDirectories: true)

guard let document = PDFDocument(url: inputURL) else {
    fputs("Unable to open PDF\n", stderr)
    exit(65)
}

var extracted = "PDF pages: \(document.pageCount)\n\n"
let maxWidth: CGFloat = 1400

for index in 0..<document.pageCount {
    guard let page = document.page(at: index) else { continue }
    extracted += "===== PAGE \(index + 1) =====\n"
    extracted += (page.string ?? "[NO EMBEDDED TEXT]") + "\n\n"

    let bounds = page.bounds(for: .mediaBox)
    let scale = maxWidth / max(bounds.width, 1)
    let size = NSSize(width: maxWidth, height: bounds.height * scale)
    let image = NSImage(size: size)
    image.lockFocus()
    NSColor.white.setFill()
    NSBezierPath(rect: NSRect(origin: .zero, size: size)).fill()
    guard let context = NSGraphicsContext.current?.cgContext else {
        image.unlockFocus()
        continue
    }
    context.saveGState()
    context.scaleBy(x: scale, y: scale)
    page.draw(with: .mediaBox, to: context)
    context.restoreGState()
    image.unlockFocus()

    guard let tiff = image.tiffRepresentation,
          let bitmap = NSBitmapImageRep(data: tiff),
          let png = bitmap.representation(using: .png, properties: [:]) else { continue }
    let pageURL = outputURL.appendingPathComponent(String(format: "page-%02d.png", index + 1))
    try png.write(to: pageURL)
}

try extracted.write(to: outputURL.appendingPathComponent("embedded-text.txt"), atomically: true, encoding: .utf8)
print("pages=\(document.pageCount)")
