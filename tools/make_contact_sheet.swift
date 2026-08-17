import Foundation
import AppKit

guard CommandLine.arguments.count == 4 else {
    fputs("Usage: make_contact_sheet.swift input-directory start-page output.png\n", stderr)
    exit(64)
}

let directory = URL(fileURLWithPath: CommandLine.arguments[1], isDirectory: true)
let start = Int(CommandLine.arguments[2]) ?? 1
let output = URL(fileURLWithPath: CommandLine.arguments[3])
let columns = 4
let rows = 4
let cell = NSSize(width: 300, height: 440)
let canvasSize = NSSize(width: cell.width * CGFloat(columns), height: cell.height * CGFloat(rows))
let canvas = NSImage(size: canvasSize)
canvas.lockFocus()
NSColor(calibratedWhite: 0.08, alpha: 1).setFill()
NSBezierPath(rect: NSRect(origin: .zero, size: canvasSize)).fill()

for offset in 0..<(columns * rows) {
    let page = start + offset
    let source = directory.appendingPathComponent(String(format: "page-%02d.png", page))
    guard let image = NSImage(contentsOf: source) else { continue }
    let column = offset % columns
    let row = offset / columns
    let x = CGFloat(column) * cell.width + 12
    let y = canvasSize.height - CGFloat(row + 1) * cell.height + 28
    let target = NSRect(x: x, y: y, width: cell.width - 24, height: cell.height - 44)
    image.draw(in: target, from: .zero, operation: .copy, fraction: 1)
    let label = "\(page)" as NSString
    label.draw(at: NSPoint(x: x, y: y - 23), withAttributes: [
        .font: NSFont.boldSystemFont(ofSize: 16),
        .foregroundColor: NSColor.white
    ])
}

canvas.unlockFocus()
guard let tiff = canvas.tiffRepresentation,
      let bitmap = NSBitmapImageRep(data: tiff),
      let png = bitmap.representation(using: .png, properties: [:]) else { exit(65) }
try png.write(to: output)
