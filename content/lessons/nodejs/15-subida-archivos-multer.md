# Subida y gestión de archivos con Multer

## ¿Por qué Multer?

Express no maneja `multipart/form-data` (el content-type de los formularios con archivos) de forma nativa. **Multer** es el middleware estándar para este propósito: intercepta la petición, procesa los archivos y los deja disponibles en `req.file` o `req.files`.

```bash
npm install multer
npm install -D @types/multer
```

---

## Disk storage vs Memory storage

### Memory storage
Los archivos se guardan en RAM como `Buffer`. Ideal para archivos pequeños o cuando vas a procesarlos antes de guardarlos (e.g., redimensionar imágenes).

```typescript
// src/lib/uploadMemory.ts
import multer from 'multer';

export const uploadMemory = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 5 * 1024 * 1024, // 5 MB
  },
});
```

### Disk storage
Los archivos se guardan directamente en disco. Puedes controlar el destino y el nombre del archivo:

```typescript
// src/lib/uploadDisk.ts
import multer from 'multer';
import path from 'node:path';
import { randomUUID } from 'node:crypto';

const storage = multer.diskStorage({
  destination: (_req, _file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (_req, file, cb) => {
    const ext = path.extname(file.originalname);
    const uniqueName = `${randomUUID()}${ext}`;
    cb(null, uniqueName);
  },
});

export const uploadDisk = multer({
  storage,
  limits: { fileSize: 10 * 1024 * 1024 }, // 10 MB
});
```

---

## Validación de tipo MIME y extensión

Multer permite filtrar archivos antes de guardarlos:

```typescript
// src/lib/upload.ts
import multer, { FileFilterCallback } from 'multer';
import { Request } from 'express';
import path from 'node:path';

const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const ALLOWED_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.webp', '.gif'];

function imageFilter(
  _req: Request,
  file: Express.Multer.File,
  cb: FileFilterCallback
): void {
  const ext = path.extname(file.originalname).toLowerCase();

  if (!ALLOWED_MIME_TYPES.includes(file.mimetype)) {
    cb(new Error(`Tipo MIME no permitido: ${file.mimetype}`));
    return;
  }

  if (!ALLOWED_EXTENSIONS.includes(ext)) {
    cb(new Error(`Extensión no permitida: ${ext}`));
    return;
  }

  cb(null, true);
}

export const uploadImage = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 5 * 1024 * 1024 },
  fileFilter: imageFilter,
});

// Para PDFs y documentos
export const uploadDocument = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 20 * 1024 * 1024 },
  fileFilter: (_req, file, cb) => {
    const allowed = ['application/pdf', 'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    cb(null, allowed.includes(file.mimetype));
  },
});
```

---

## Single, array y fields

```typescript
// src/routes/upload.routes.ts
import { Router, Request, Response, NextFunction } from 'express';
import { uploadImage, uploadDocument } from '../lib/upload';
import { fileService } from '../services/FileService';

const router = Router();

// Un solo archivo en el campo "avatar"
router.post(
  '/avatar',
  uploadImage.single('avatar'),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.file) throw new Error('No se recibió ningún archivo');
      const url = await fileService.saveAvatar(req.file, req.user!.sub);
      res.json({ url });
    } catch (err) { next(err); }
  }
);

// Hasta 10 imágenes en el campo "photos"
router.post(
  '/photos',
  uploadImage.array('photos', 10),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const files = req.files as Express.Multer.File[];
      if (!files?.length) throw new Error('No se recibieron archivos');
      const urls = await fileService.savePhotos(files, req.user!.sub);
      res.json({ urls });
    } catch (err) { next(err); }
  }
);

// Campos mixtos: una portada + hasta 5 fotos del producto
router.post(
  '/product',
  uploadImage.fields([
    { name: 'cover', maxCount: 1 },
    { name: 'gallery', maxCount: 5 },
  ]),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const files = req.files as Record<string, Express.Multer.File[]>;
      const cover = files['cover']?.[0];
      const gallery = files['gallery'] ?? [];
      const result = await fileService.saveProduct(cover, gallery);
      res.json(result);
    } catch (err) { next(err); }
  }
);

export default router;
```

---

## Procesamiento de imágenes con Sharp

**Sharp** es la librería más rápida para procesar imágenes en Node.js. Usa libvips internamente.

```bash
npm install sharp
npm install -D @types/sharp
```

```typescript
// src/services/FileService.ts
import sharp from 'sharp';
import { randomUUID } from 'node:crypto';
import path from 'node:path';
import fs from 'node:fs/promises';

const UPLOAD_DIR = path.join(process.cwd(), 'public', 'uploads');

export class FileService {
  async saveAvatar(file: Express.Multer.File, userId: string): Promise<string> {
    await fs.mkdir(UPLOAD_DIR, { recursive: true });

    const filename = `avatar-${userId}-${randomUUID()}.webp`;
    const outputPath = path.join(UPLOAD_DIR, filename);

    // Convertir a WebP, redimensionar a 200x200, comprimir
    await sharp(file.buffer)
      .resize(200, 200, { fit: 'cover', position: 'center' })
      .webp({ quality: 85 })
      .toFile(outputPath);

    return `/uploads/${filename}`;
  }

  async savePhotos(files: Express.Multer.File[], _userId: string): Promise<string[]> {
    const urls: string[] = [];

    for (const file of files) {
      const filename = `photo-${randomUUID()}.webp`;
      const outputPath = path.join(UPLOAD_DIR, filename);

      await sharp(file.buffer)
        .resize(1200, 800, { fit: 'inside', withoutEnlargement: true })
        .webp({ quality: 80 })
        .toFile(outputPath);

      urls.push(`/uploads/${filename}`);
    }

    return urls;
  }
}

export const fileService = new FileService();
```

---

## Subir a AWS S3

Para producción es mejor almacenar en object storage como S3:

```bash
npm install @aws-sdk/client-s3 @aws-sdk/s3-request-presigner
```

```typescript
// src/lib/s3.ts
import {
  S3Client,
  PutObjectCommand,
  GetObjectCommand,
  DeleteObjectCommand,
} from '@aws-sdk/client-s3';
import { getSignedUrl } from '@aws-sdk/s3-request-presigner';
import { env } from '../config/env';

const s3 = new S3Client({
  region: env.AWS_REGION,
  credentials: {
    accessKeyId: env.AWS_ACCESS_KEY_ID,
    secretAccessKey: env.AWS_SECRET_ACCESS_KEY,
  },
});

export async function uploadToS3(
  buffer: Buffer,
  key: string,
  contentType: string
): Promise<string> {
  await s3.send(
    new PutObjectCommand({
      Bucket: env.S3_BUCKET,
      Key: key,
      Body: buffer,
      ContentType: contentType,
    })
  );
  return `https://${env.S3_BUCKET}.s3.${env.AWS_REGION}.amazonaws.com/${key}`;
}

export async function getPresignedUrl(key: string, expiresIn = 3600): Promise<string> {
  return getSignedUrl(
    s3,
    new GetObjectCommand({ Bucket: env.S3_BUCKET, Key: key }),
    { expiresIn }
  );
}

export async function deleteFromS3(key: string): Promise<void> {
  await s3.send(new DeleteObjectCommand({ Bucket: env.S3_BUCKET, Key: key }));
}
```

Servicio que combina Sharp + S3:

```typescript
async saveAvatarToS3(file: Express.Multer.File, userId: string): Promise<string> {
  const processedBuffer = await sharp(file.buffer)
    .resize(200, 200, { fit: 'cover' })
    .webp({ quality: 85 })
    .toBuffer();

  const key = `avatars/${userId}/${randomUUID()}.webp`;
  return uploadToS3(processedBuffer, key, 'image/webp');
}
```

---

## Manejo de errores de Multer

```typescript
// src/middlewares/multerErrorHandler.ts
import { Request, Response, NextFunction } from 'express';
import multer from 'multer';

export function multerErrorHandler(
  err: Error,
  _req: Request,
  res: Response,
  next: NextFunction
): void {
  if (err instanceof multer.MulterError) {
    if (err.code === 'LIMIT_FILE_SIZE') {
      res.status(413).json({ message: 'El archivo supera el tamaño máximo permitido' });
      return;
    }
    if (err.code === 'LIMIT_FILE_COUNT') {
      res.status(400).json({ message: 'Demasiados archivos' });
      return;
    }
    res.status(400).json({ message: `Error de upload: ${err.message}` });
    return;
  }
  next(err);
}
```

Registrarlo **antes** del `errorHandler` general:

```typescript
app.use(multerErrorHandler);
app.use(errorHandler);
```

---

## Resumen

| Escenario | Solución |
|---|---|
| Imagen de perfil | `upload.single('field')` + Sharp resize + S3 |
| Galería de fotos | `upload.array('field', max)` + procesamiento en bucle |
| Formulario con múltiples tipos | `upload.fields([...])` |
| Validación de tipo | `fileFilter` en la config de Multer |
| Almacenamiento local | `diskStorage` con nombre único UUID |
| Almacenamiento en la nube | Buffer → Sharp → S3 `PutObjectCommand` |
| Archivos privados | Pre-signed URLs con expiración |
