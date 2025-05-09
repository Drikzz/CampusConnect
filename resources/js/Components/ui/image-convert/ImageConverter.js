/**
 * Utility functions for image conversion and processing
 * This helps with converting blob URLs to base64 and preparing images for
 * uploading to the server
 */

/**
 * Convert a Blob URL to a Base64 string
 * @param {string} blobUrl - The blob URL to convert
 * @returns {Promise<string>} - Promise resolving to base64 data
 */
export const blobUrlToBase64 = async (blobUrl) => {
  try {
    // Fetch the blob from the URL
    const response = await fetch(blobUrl);
    const blob = await response.blob();
    
    // Use FileReader to convert to base64
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  } catch (error) {
    console.error("Error converting blob URL to base64:", error);
    throw error;
  }
};

/**
 * Get file extension from a blob URL or mime type
 * @param {string|object} blob - Blob URL or Blob object
 * @param {string} fallbackMimeType - Fallback mime type if not detectable
 * @returns {string} - File extension (jpg, png, etc.)
 */
export const getFileExtensionFromBlob = async (blob, fallbackMimeType = 'image/jpeg') => {
  try {
    let mimeType;
    
    if (typeof blob === 'string' && blob.startsWith('blob:')) {
      // It's a blob URL, fetch the blob first
      const response = await fetch(blob);
      const blobObj = await response.blob();
      mimeType = blobObj.type || fallbackMimeType;
    } else if (blob instanceof Blob) {
      // It's already a Blob object
      mimeType = blob.type || fallbackMimeType;
    } else {
      // Default to fallback
      mimeType = fallbackMimeType;
    }
    
    // Map common mime types to extensions
    const mimeToExt = {
      'image/jpeg': 'jpg',
      'image/jpg': 'jpg',
      'image/png': 'png',
      'image/gif': 'gif',
      'image/webp': 'webp',
      'image/bmp': 'bmp',
      'image/svg+xml': 'svg'
    };
    
    return mimeToExt[mimeType] || 'jpg';
  } catch (error) {
    console.error("Error getting file extension:", error);
    return 'jpg'; // Default fallback
  }
};

/**
 * Prepare blob image for form submission
 * @param {string|object} blobImage - Blob URL or object with URL
 * @returns {Promise<object>} - Object with base64 data and extension
 */
export const prepareBlobImageForSubmission = async (blobImage) => {
  try {
    const url = typeof blobImage === 'object' && blobImage.url ? blobImage.url : blobImage;
    
    if (!url || (typeof url !== 'string')) {
      throw new Error('Invalid blob image format');
    }
    
    // Convert to base64
    const base64Data = await blobUrlToBase64(url);
    
    // Get file extension
    const extension = await getFileExtensionFromBlob(url);
    
    return {
      data: base64Data,
      extension: extension
    };
  } catch (error) {
    console.error("Error preparing blob for submission:", error);
    throw error;
  }
};

/**
 * Process all blob images in an offered item
 * @param {Object} item - Offered item object
 * @returns {Promise<Object>} - Processed item with blob_images array
 */
export const processOfferedItemImages = async (item) => {
  if (!item.images || !Array.isArray(item.images)) {
    return item;
  }
  
  // Find blob images
  const blobImages = item.images.filter(img => {
    return (typeof img === 'string' && img.startsWith('blob:')) || 
           (typeof img === 'object' && img.isBlob);
  });
  
  // If no blob images, return original item
  if (blobImages.length === 0) {
    return item;
  }
  
  // Process each blob image
  const processedBlobImages = await Promise.all(
    blobImages.map(async blobImage => {
      return await prepareBlobImageForSubmission(blobImage);
    })
  );
  
  // Add processed blob images to item
  return {
    ...item,
    blob_images: processedBlobImages
  };
};

export default {
  blobUrlToBase64,
  getFileExtensionFromBlob,
  prepareBlobImageForSubmission,
  processOfferedItemImages
};
