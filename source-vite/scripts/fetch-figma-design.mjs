#!/usr/bin/env node
/**
 * Fetch design context from Figma API and output design-context.json
 * Usage: node scripts/fetch-figma-design.mjs <url>
 *        node scripts/fetch-figma-design.mjs <fileKey> <nodeId>
 *
 * Requires FIGMA_TOKEN or FIGMA_ACCESS_TOKEN in .env
 */

import 'dotenv/config'
import { writeFileSync } from 'fs'
import { resolve, dirname } from 'path'
import { fileURLToPath } from 'url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT = resolve(__dirname, '..')

const FIGMA_API = 'https://api.figma.com/v1'

const token = process.env.FIGMA_ACCESS_TOKEN || process.env.FIGMA_TOKEN
if (!token) {
  console.error('Error: Add FIGMA_ACCESS_TOKEN or FIGMA_TOKEN to .env')
  process.exit(1)
}

function parseInput(input) {
  const args = input.trim().split(/\s+/)
  if (args.length === 1) {
    const url = args[0]
    const urlMatch = url.match(/figma\.com\/design\/([^/]+)[^?]*\?.*node-id=(\d+)-(\d+)/)
    if (urlMatch) {
      return { fileKey: urlMatch[1], nodeId: `${urlMatch[2]}:${urlMatch[3]}` }
    }
    const devMatch = url.match(/figma\.com\/file\/([^/]+)[^?]*\?.*node-id=(\d+)-(\d+)/)
    if (devMatch) {
      return { fileKey: devMatch[1], nodeId: `${devMatch[2]}:${devMatch[3]}` }
    }
  }
  if (args.length >= 2) {
    const nodeId = args[1].includes(':') ? args[1] : args[1].replace('-', ':')
    return { fileKey: args[0], nodeId }
  }
  return null
}

function rgbaToHex(r, g, b, a = 1) {
  const toHex = (n) =>
    Math.round(n * 255)
      .toString(16)
      .padStart(2, '0')
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

function slugify(str) {
  return (
    str
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '') || 'color'
  )
}

const colorsSeen = new Map()
const typographySeen = new Map()
const spacingValues = new Set()

function summarizeNode(n) {
  const summary = {
    id: n.id,
    name: n.name,
    type: n.type,
    bounds: n.absoluteBoundingBox
      ? {
          x: Math.round(n.absoluteBoundingBox.x),
          y: Math.round(n.absoluteBoundingBox.y),
          width: Math.round(n.absoluteBoundingBox.width),
          height: Math.round(n.absoluteBoundingBox.height),
        }
      : null,
    layout: n.layoutMode
      ? {
          mode: n.layoutMode,
          padding: {
            left: n.paddingLeft,
            right: n.paddingRight,
            top: n.paddingTop,
            bottom: n.paddingBottom,
          },
          itemSpacing: n.itemSpacing,
        }
      : null,
    children: [],
  }
  if (n.children && Array.isArray(n.children)) {
    summary.children = n.children.map(summarizeNode)
  }
  return summary
}

function extractFromNode(node, prefix = '') {
  const result = { colors: {}, typography: {}, spacing: {}, nodes: [] }

  function walk(n, depth = 0, parentKey = '') {
    if (!n) return

    const name = n.name || 'unnamed'
    const safeName = slugify(name)
    const key = parentKey ? `${parentKey}-${safeName}` : safeName

    if (n.fills && Array.isArray(n.fills)) {
      for (const fill of n.fills) {
        if (fill.type === 'SOLID' && fill.color) {
          const { r, g, b, a = 1 } = fill.color
          const hex = rgbaToHex(r, g, b, a)
          const colorKey = `${key}-fill`
          if (!colorsSeen.has(hex)) {
            colorsSeen.set(hex, colorKey)
            result.colors[colorKey] = hex
          }
        }
      }
    }
    if (n.strokes && Array.isArray(n.strokes)) {
      for (const stroke of n.strokes) {
        if (stroke.type === 'SOLID' && stroke.color) {
          const { r, g, b, a = 1 } = stroke.color
          const hex = rgbaToHex(r, g, b, a)
          const colorKey = `${key}-stroke`
          if (!colorsSeen.has(hex)) {
            colorsSeen.set(hex, colorKey)
            result.colors[colorKey] = hex
          }
        }
      }
    }
    if (n.backgroundColor && typeof n.backgroundColor === 'object') {
      const { r, g, b, a = 1 } = n.backgroundColor
      const hex = rgbaToHex(r, g, b, a)
      const colorKey = `${key}-bg`
      if (!colorsSeen.has(hex)) {
        colorsSeen.set(hex, colorKey)
        result.colors[colorKey] = hex
      }
    }

    if (n.style) {
      const style = n.style
      const styleKey = JSON.stringify(style)
      const typoKey = typographySeen.get(styleKey) || `typo-${depth}-${safeName}`
      if (!typographySeen.has(styleKey)) {
        typographySeen.set(styleKey, typoKey)
        result.typography[typoKey] = {
          fontFamily: style.fontFamily,
          fontSize: style.fontSize,
          fontWeight: style.fontWeight,
          lineHeightPx: style.lineHeightPx,
          letterSpacing: style.letterSpacing,
        }
      }
    }

    if (n.absoluteBoundingBox) {
      const b = n.absoluteBoundingBox
      const w = b.width || 0
      const h = b.height || 0
      if (w > 0) spacingValues.add(Math.round(w))
      if (h > 0) spacingValues.add(Math.round(h))
    }
    if (typeof n.paddingLeft === 'number') spacingValues.add(Math.round(n.paddingLeft))
    if (typeof n.paddingRight === 'number') spacingValues.add(Math.round(n.paddingRight))
    if (typeof n.paddingTop === 'number') spacingValues.add(Math.round(n.paddingTop))
    if (typeof n.paddingBottom === 'number') spacingValues.add(Math.round(n.paddingBottom))
    if (typeof n.itemSpacing === 'number') spacingValues.add(Math.round(n.itemSpacing))

    if (n.children && Array.isArray(n.children)) {
      for (const child of n.children) walk(child, depth + 1, key)
    }
  }

  walk(node)
  result.nodes.push(summarizeNode(node))
  result.spacing = Array.from(spacingValues)
    .filter((v) => v > 0 && v < 5000)
    .sort((a, b) => a - b)
  return result
}

async function fetchFigma(fileKey, nodeId) {
  const headers = { 'X-Figma-Token': token }
  const nodeIds = encodeURIComponent(nodeId)

  const nodesRes = await fetch(`${FIGMA_API}/files/${fileKey}/nodes?ids=${nodeIds}`, { headers })
  if (!nodesRes.ok) {
    const err = await nodesRes.text()
    if (nodesRes.status === 401 || nodesRes.status === 403) {
      throw new Error('Invalid token or no access. Check FIGMA_TOKEN in .env')
    }
    if (nodesRes.status === 404) {
      throw new Error('File or node not found')
    }
    throw new Error(`Figma API error ${nodesRes.status}: ${err}`)
  }

  const nodesData = await nodesRes.json()
  const nodeData = nodesData.nodes?.[nodeId]
  if (!nodeData || !nodeData.document) {
    throw new Error(`Node ${nodeId} not found in response`)
  }

  const doc = nodeData.document
  colorsSeen.clear()
  typographySeen.clear()
  spacingValues.clear()
  const extracted = extractFromNode(doc)

  let styles = {}
  try {
    const stylesRes = await fetch(`${FIGMA_API}/files/${fileKey}/styles`, { headers })
    if (stylesRes.ok) {
      const stylesData = await stylesRes.json()
      styles = stylesData.meta?.styles || {}
    }
  } catch {
    // styles optional
  }

  let images = []
  try {
    const imagesRes = await fetch(`${FIGMA_API}/images/${fileKey}?ids=${nodeIds}&format=svg,png`, {
      method: 'GET',
      headers,
    })
    if (imagesRes.ok) {
      const imagesData = await imagesRes.json()
      const urls = imagesData.images || {}
      for (const [id, url] of Object.entries(urls)) {
        if (url) images.push({ nodeId: id, url, suggestedFilename: `${id.replace(':', '-')}.svg` })
      }
    }
  } catch {
    // images optional
  }

  const designContext = {
    source: {
      fileKey,
      nodeId,
      fetchedAt: new Date().toISOString(),
    },
    colors: extracted.colors,
    typography: extracted.typography,
    spacing: { values: extracted.spacing },
    nodes: extracted.nodes,
    images,
    styles: Object.keys(styles).length ? styles : undefined,
  }

  return designContext
}

async function main() {
  const input = process.argv.slice(2).join(' ')
  const parsed = parseInput(input)
  if (!parsed) {
    console.error(
      'Usage: npm run figma:fetch -- "https://www.figma.com/design/<fileKey>/<file>?node-id=313-823"',
    )
    console.error('   or: npm run figma:fetch -- <fileKey> 313:823')
    process.exit(1)
  }

  const { fileKey, nodeId } = parsed
  console.log(`Fetching Figma design: fileKey=${fileKey}, nodeId=${nodeId}`)

  try {
    const designContext = await fetchFigma(fileKey, nodeId)
    const outPath = resolve(ROOT, 'design-context.json')
    writeFileSync(outPath, JSON.stringify(designContext, null, 2), 'utf8')
    console.log(`Done. Written to ${outPath}`)
    console.log(`  Colors: ${Object.keys(designContext.colors).length}`)
    console.log(`  Typography: ${Object.keys(designContext.typography).length}`)
    console.log(`  Images: ${designContext.images.length}`)
  } catch (err) {
    console.error(err.message)
    process.exit(1)
  }
}

main()
