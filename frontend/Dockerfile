# Team Management Platform - Frontend Dockerfile
# Node 20 + Vite Development Server

FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install dependencies for native modules
RUN apk add --no-cache \
    python3 \
    make \
    g++ \
    git

# Create non-root user
RUN addgroup -g 1001 nodegroup && \
    adduser -u 1001 -D -G nodegroup nodeuser

# Set ownership of the working directory
RUN chown -R nodeuser:nodegroup /app

# Switch to non-root user
USER nodeuser

# Copy package files
COPY --chown=nodeuser:nodegroup package*.json ./

# Install dependencies
RUN npm install

# Copy application files
COPY --chown=nodeuser:nodegroup . .

# Expose Vite dev server port
EXPOSE 5173

# Environment variables for HMR polling (Windows/Linux compatibility)
ENV CHOKIDAR_USEPOLLING=true
ENV WATCHPACK_POLLING=true

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=30s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:5173 || exit 1

# Start Vite dev server with host binding
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
