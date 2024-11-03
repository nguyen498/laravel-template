export const getParam = (s?: string): URLSearchParams | string => {
  if (s) {
    return new URLSearchParams(window.location.search).get(s)
  }
  return new URLSearchParams(window.location.search)
}

export const stringToBase64 = (str: string): string => {
  const encoder = new TextEncoder()
  // Mã hóa chuỗi thành một Uint8Array
  const data = encoder.encode(str)
  // Chuyển đổi Uint8Array thành chuỗi Base64
  return btoa(String.fromCharCode(...data))
}

export const formatCurrency = (value: string | number) => {
  // return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(value.replace(/[^0-9]/g, '')));
  return new Intl.NumberFormat('vi-VN').format(Number(`${value}`.replace(/[^0-9]/g, '')))
}

export const removeVietnameseTones = (str: string) => {
  return str
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
}

/**
 * Định dạng số thành chuỗi tiền tệ VNĐ.
 * @param {number} value - Số cần định dạng.
 * @returns {string} - Chuỗi tiền tệ VNĐ đã được định dạng.
 */
export function formatCurrencyFull(value) {
  return (
    new Intl.NumberFormat('en-US', {
      // style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0 // Không hiển thị phần thập phân
    }).format(value) + ' VNĐ'
  )

  // Định dạng số với dấu phân cách hàng nghìn
  // const formattedNumber = value.toLocaleString('en-US').replace(/,/g, '.')
  // return `${formattedNumber} VNĐ`
}
