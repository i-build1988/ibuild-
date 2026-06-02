import { Readable } from "node:stream";
import { api } from "../../server.mjs";

export async function handler(event) {
  const body = event.isBase64Encoded
    ? Buffer.from(event.body || "", "base64")
    : Buffer.from(event.body || "", "utf8");
  const req = Readable.from(body.length ? [body] : []);
  req.method = event.httpMethod;
  req.headers = event.headers || {};

  let statusCode = 200;
  let headers = {};
  let responseBody = "";
  const res = {
    writeHead(status, nextHeaders = {}) {
      statusCode = status;
      headers = { ...headers, ...nextHeaders };
    },
    end(chunk = "") {
      responseBody = Buffer.isBuffer(chunk) ? chunk.toString("utf8") : String(chunk);
    }
  };

  const path = normalizePath(event.path || "/api/bootstrap");
  const query = event.rawQuery ? `?${event.rawQuery}` : "";
  const url = new URL(`${path}${query}`, "https://netlify.local");

  await api(req, res, url);

  return {
    statusCode,
    headers,
    body: responseBody
  };
}

function normalizePath(pathname) {
  if (pathname.startsWith("/.netlify/functions/api")) {
    const rest = pathname.slice("/.netlify/functions/api".length);
    return `/api${rest || ""}`;
  }
  return pathname.startsWith("/api/") ? pathname : `/api${pathname}`;
}
